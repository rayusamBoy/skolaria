<?php

namespace App\Rules;

use App\Models\Division;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class UniqueDivisionRange implements ValidationRule
{
    public function __construct(
        private readonly ?int $ignoreId = null,
        private readonly ?int $classTypeId = null,
        private readonly ?string $name = null,
        private readonly int|float|null $pointsFrom = null,
        private readonly int|float|null $pointsTo = null,
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $base = Division::query()
            ->where('class_type_id', $this->classTypeId)
            ->active()
            ->when($this->ignoreId, fn ($q) => $q->whereKeyNot($this->ignoreId));

        // Name must be unique within the class type.
        if ((clone $base)->where('name', $this->name)->exists()) {
            $fail(__('validation.division.name_taken', ['name' => $this->name]));

            return; // First failure is enough; skip the second query.
        }

        // Point range must not overlap any other division in the
        // same class type. Note: `name` is intentionally NOT part of
        // this check — two divisions with different names must still
        // have disjoint point ranges.
        $overlaps = (clone $base)
            ->where('points_from', '<=', $this->pointsTo)
            ->where('points_to', '>=', $this->pointsFrom)
            ->exists();

        if ($overlaps) {
            $fail(__('validation.division.range_overlap', [
                'from' => $this->pointsFrom,
                'to' => $this->pointsTo,
            ]));
        }
    }
}
