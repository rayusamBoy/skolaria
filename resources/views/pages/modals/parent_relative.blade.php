<!-- Modal -->
<div
    class="modal fade"
    id="parent-relative-modal"
    tabindex="-1"
    aria-labelledby="close-relative-info-heading"
    aria-hidden="true"
>
    @php $p_relative = Qs::getParentRelativeByUserId($user->id) @endphp
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-center" id="close-relative-info-heading">
                    Relative Info of {{ $user->name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">Close</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table-bordered table-responsive table">
                    <tr>
                        <td class="font-weight-bold">Relative Name</td>
                        <td>{{ $p_relative->name }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Relation with Parent</td>
                        <td>{{ $p_relative->relation }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Relative Primary Mobile</td>
                        <td><a href="tel:{{ $p_relative->primary_mobile }}">{{ $p_relative->primary_mobile }}</a></td>
                    </tr>
                    @if (isset($p_relative->secondary_mobile))
                        <tr>
                            <td class="font-weight-bold">Relative Secondary Mobile</td>
                            <td>
                                <a href="tel:{{ $p_relative->secondary_mobile }}">{{ $p_relative->secondary_mobile }}</a>
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
