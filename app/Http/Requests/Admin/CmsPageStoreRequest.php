<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesCmsBilingualPublish;
use App\Models\CmsPage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CmsPageStoreRequest extends FormRequest
{
    use ValidatesCmsBilingualPublish;

    public function authorize(): bool
    {
        return $this->user()?->can('create', CmsPage::class) ?? false;
    }

    public function withValidator(Validator $validator): void
    {
        $this->withCmsBilingualPublishValidator($validator);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('cms_pages', 'slug')->where(fn ($q) => $q->where('locale', $this->input('locale'))),
            ],
            'locale' => ['required', 'string', Rule::in(['en', 'ar'])],
            'title' => ['required', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'string', 'max:2048', 'regex:/^(https?:\\/\\/\\S+|\\/\\S*)$/i'],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'body' => ['required', 'string'],
            'status' => ['required', 'string', Rule::in([
                CmsPage::STATUS_DRAFT,
                CmsPage::STATUS_IN_REVIEW,
                CmsPage::STATUS_PUBLISHED,
                CmsPage::STATUS_ARCHIVED,
            ])],
            'published_at' => [
                'nullable',
                'date',
                Rule::requiredIf(fn () => $this->input('status') === CmsPage::STATUS_PUBLISHED),
            ],
            'show_on_home' => ['boolean'],
            'allow_partial_locale_publish' => ['sometimes', 'boolean'],
        ];
    }
}
