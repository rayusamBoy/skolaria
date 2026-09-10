<?php

namespace App\Http\Controllers;

use App\Helpers\Pay;
use App\Helpers\Qs;
use App\Http\Requests\Payment\PaymentCreate;
use App\Http\Requests\Payment\PaymentUpdate;
use App\Notifications\StudentPaymentPaid;
use App\Repositories\MyClassRepo;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected string $year;

    public function __construct(protected MyClassRepo $my_class, protected PaymentRepo $pay, protected StudentRepo $student)
    {
        $this->permissionMap = config('permissions.names.tenant.payments');
        parent::__construct();
        $this->year = Qs::getCurrentSession();
    }

    /**
     * Display payment years selection.
     */
    public function index(): View
    {
        $data['selected'] = false;
        $data['years'] = $this->pay->getDistinctPaymentYears();

        return view('pages.payments.index', $data);
    }

    /**
     * Show payments for a specific year.
     */
    public function show(string $year): View|RedirectResponse
    {
        $payments = $this->pay->getPaymentsByConditions(['year' => $year])->get();

        if ($payments->isEmpty()) {
            return Qs::goWithDanger('payments.index');
        }

        $data['payments'] = $payments;
        $data['selected'] = true;
        $data['years'] = $this->pay->getDistinctPaymentYears();
        $data['year'] = $year;

        return view('pages.payments.index', $data);
    }

    /**
     * Redirect to the selected year's payments.
     */
    public function select_year(Request $req): RedirectResponse
    {
        return Qs::goToRoute(['payments.show', $req->year]);
    }

    /**
     * Show form to create a new payment.
     */
    public function create(): View
    {
        return view('pages.payments.create');
    }

    /**
     * Show invoice for a student, optionally filtered by year.
     */
    public function invoice(int $st_id, $year = null): View|RedirectResponse
    {
        if (! $st_id) {
            return Qs::goWithDanger();
        }

        $records = $year
            ? $this->pay->getStudentPaymentRecords($st_id, $year)
            : $this->pay->getStudentPaymentRecords($st_id);
        $records = $records->get()->whereNotNull('payment');

        $data['sr'] = $this->student->getActiveStudentsQueryWithRelations(['user_id' => $st_id])->first();
        $data['uncleared'] = $records->where('is_paid', false);
        $data['cleared'] = $records->where('is_paid', true);
        $data['year'] = $year;

        return view('pages.payments.invoice', $data);
    }

    /**
     * Show payment status for a student (current year enforced).
     */
    public function status(int $st_id, $year = null): View|RedirectResponse
    {
        if ($year && $year != $this->year) {
            return back()->with('flash_danger', __('msg.year_invalid'));
        }

        if (! $st_id) {
            return Qs::goWithDanger();
        }

        $records = $year
            ? $this->pay->getStudentPaymentRecords($st_id, $year)
            : $this->pay->getStudentPaymentRecords($st_id);
        $records = $records->get()->whereNotNull('payment');

        $data['sr'] = $this->student->getActiveStudentsQueryWithRelations(['user_id' => $st_id])->first();
        $data['uncleared'] = $records->where('is_paid', false);
        $data['cleared'] = $records->where('is_paid', true);

        return view('pages.payments.status', $data);
    }

    /**
     * Display receipts for a payment record, optionally mark notification read.
     */
    public function receipts(int $pr_id, $notification_id = null): View|RedirectResponse
    {
        if (! $pr_id) {
            return Qs::goWithDanger();
        }

        try {
            $record = $this->pay->getPaymentRecords(['id' => $pr_id])->with('receipt')->firstOrFail();
        } catch (ModelNotFoundException $ex) {
            return back()->with('flash_danger', __('msg.rnf'));
        }

        $data['pr'] = $record;
        $data['sr'] = $this->student->getActiveStudentsQueryWithRelations(['user_id' => $record->student_id])->first();
        $data['receipts'] = $record->receipt;
        $data['payment'] = $record->payment;
        $data['settings'] = Qs::getSettings();

        if ($notification_id !== null) {
            app(NotificationController::class)->mark_as_read($notification_id, false);
        }

        return view('pages.payments.receipt', $data);
    }

    /**
     * Process a payment for an outstanding record.
     */
    public function pay_now(Request $req, int $pr_id): JsonResponse
    {
        Validator::make($req->toArray(), [
            'amount_paid' => 'required|numeric',
        ], [], ['amount_paid' => 'Amount Paid'])->validate();

        $record = $this->pay->findPaymentRecord($pr_id);
        $payment = $this->pay->findPayment($record->payment_id);
        $amt_p = $record->amount_paid + $req->amount_paid;
        $bal = $payment->amount - $amt_p;

        $this->pay->updatePaymentRecord($pr_id, [
            'amount_paid' => $amt_p,
            'balance' => $bal,
            'is_paid' => $bal < 1 ? true : false,
        ]);

        $receipt_data = [
            'amount_paid' => $req->amount_paid,
            'balance' => $bal,
            'payment_record_id' => $pr_id,
            'year' => $this->year,
        ];
        $receipt = $this->pay->createReceipt($receipt_data);

        $student_record = $this->student->getActiveStudentsQueryWithRelations(['user_id' => $record->student_id])
            ->with(['my_parent', 'user'])
            ->first();

        $parent = $student_record?->my_parent;
        if ($parent) {
            $parent->notify(new StudentPaymentPaid($receipt, $student_record));
        }

        return Qs::jsonUpdateOk();
    }

    /**
     * Show student payment management for a class.
     */
    public function manage(?int $class_id = null): View|RedirectResponse
    {
        $data['selected'] = false;

        if ($class_id) {
            $students = $this->student->getActiveStudentsQueryWithRelations(['my_class_id' => $class_id])
                ->get()
                ->whereNotNull('user')
                ->sortBy('user.name');

            if ($students->isEmpty()) {
                return Qs::goWithDanger('payments.manage');
            }

            $data['selected'] = true;
            $data['my_class_id'] = $class_id;
            $data['students'] = $students;
        }

        return view('pages.payments.manage', $data);
    }

    /**
     * Select a class and generate payment records for all its students.
     */
    public function select_class(Request $req): RedirectResponse
    {
        Validator::make($req->toArray(), [
            'my_class_id' => 'required|exists:my_classes,id',
        ], [], ['my_class_id' => 'Class'])->validate();

        $class_id = $req->my_class_id;

        $class_payments = $this->pay->getPaymentsByConditions([
            'my_class_id' => $class_id,
            'year' => $this->year,
        ])->get();

        $global_payments = $this->pay->getGlobalPayments(['year' => $this->year])->get();
        $payments = $global_payments->isNotEmpty() ? $class_payments->merge($global_payments) : $class_payments;

        $students = $this->student->getActiveStudentsQueryWithRelations(['my_class_id' => $class_id])->get();

        if ($payments->isNotEmpty() && $students->isNotEmpty()) {
            foreach ($payments as $payment) {
                foreach ($students as $student) {
                    $record = $this->pay->firstOrCreatePaymentRecord([
                        'student_id' => $student->user_id,
                        'payment_id' => $payment->id,
                        'year' => $this->year,
                    ]);

                    if (! $record->reference_number) {
                        $record->update(['reference_number' => mt_rand(100000, 99999999)]);
                    }
                }
            }
        }

        return Qs::goToRoute(['payments.manage', $class_id]);
    }

    /**
     * Store a new payment definition.
     */
    public function store(PaymentCreate $req): JsonResponse
    {
        $data = $req->all();
        $data['year'] = $this->year;
        $data['reference_number'] = Pay::generateReferenceCode();

        $this->pay->createPayment($data);

        return Qs::jsonStoreOk();
    }

    /**
     * Show the form for editing a payment definition.
     */
    public function edit(int $id): View|RedirectResponse
    {
        $payment = $this->pay->findPayment($id);

        return $payment !== null
            ? view('pages.payments.edit', ['payment' => $payment])
            : Qs::goWithDanger('payments.index');
    }

    /**
     * Update the specified payment definition.
     */
    public function update(PaymentUpdate $req, int $id): JsonResponse
    {
        $this->pay->updatePayment($id, $req->except(['_method', '_token']));

        return Qs::jsonUpdateOk();
    }

    /**
     * Soft‑delete a payment definition.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->pay->findPayment($id)->delete();

        return Qs::redirectDeleteSuccess('payments.index');
    }

    /**
     * Reset a payment record (clear amount paid and receipts).
     */
    public function reset_record(int $id): RedirectResponse
    {
        $this->pay->updatePaymentRecord($id, [
            'amount_paid' => 0,
            'balance' => 0,
            'is_paid' => false,
        ]);
        $this->pay->deleteReceipts(['payment_record_id' => $id]);

        return back()->with('flash_success', __('msg.update_ok'));
    }
}
