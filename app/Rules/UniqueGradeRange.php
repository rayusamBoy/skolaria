<?php

namespace App\Rules;

use App\Models\Grade;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueGradeRange implements ValidationRule
{
    public function __construct(
        private readonly ?int $ignoreId = null,
        private readonly ?int $classTypeId = null,
        private readonly ?string $name = null,
        private readonly int|float|null $markFrom = null,
        private readonly int|float|null $markTo = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $base = Grade::query()
            ->where('class_type_id', $this->classTypeId)
            ->active()
            ->when($this->ignoreId, fn ($q) => $q->whereKeyNot($this->ignoreId));
\Log::debug($this->ignoreId);
        if ((clone $base)->where('name', $this->name)->exists()) {
            $fail(__('validation.grade.name_taken', ['name' => $this->name]));

            return;
        }

        if ((clone $base)
            ->where('mark_from', '<=', $this->markTo)
            ->where('mark_to', '>=', $this->markFrom)
            ->exists()
        ) {
            $fail(__('validation.grade.range_overlap', [
                'from' => $this->markFrom,
                'to' => $this->markTo,
            ]));
        }
    }
}
