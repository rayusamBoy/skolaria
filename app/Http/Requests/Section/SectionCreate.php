<?php

namespace App\Http\Requests\Section;

use App\Helpers\Qs;
use App\Models\Section;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SectionCreate extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique(Section::class)->where(fn($query) => $query->where('name', $this->name)->where('my_class_id', $this->my_class_id))
            ],
            'my_class_id' => 'required',
            'teacher_id' => 'sometimes|nullable|exists:users,id',
        ];
    }

    public function attributes()
    {
        return [
            'my_class_id' => 'Class',
            'teacher_id' => 'Teacher',
        ];
    }

    protected function getValidatorInstance()
    {
        $input = $this->all();

        $input['teacher_id'] = $input['teacher_id'] ? Qs::decodeHash($input['teacher_id']) : null;

        $this->getInputSource()->replace($input);

        return parent::getValidatorInstance();
    }

    public function messages()
    {
        return [
            'name.unique' => 'The name has already been taken for the selected class.',
        ];
    }
}
