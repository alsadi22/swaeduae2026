<?php

namespace App\Http\Requests\Admin\Concerns;

use App\Models\CmsPage;
use Illuminate\Validation\Validator;

trait ValidatesCmsBilingualPublish
{
    protected function withCmsBilingualPublishValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if ($this->input('status') !== CmsPage::STATUS_PUBLISHED) {
                return;
            }

            if ($this->boolean('allow_partial_locale_publish')) {
                return;
            }

            $slug = $this->input('slug');
            $locale = $this->input('locale');

            if (! is_string($slug) || ! is_string($locale)) {
                return;
            }

            $otherLocale = $locale === 'en' ? 'ar' : 'en';

            $sibling = CmsPage::query()
                ->where('slug', $slug)
                ->where('locale', $otherLocale)
                ->first();

            if ($sibling === null) {
                $validator->errors()->add(
                    'status',
                    __('Cannot publish until the matching :locale page exists for this slug.', ['locale' => strtoupper($otherLocale)])
                );

                return;
            }

            if (trim((string) $sibling->title) === '' || trim((string) $sibling->body) === '') {
                $validator->errors()->add(
                    'status',
                    __('Cannot publish until the :locale version has a title and body.', ['locale' => strtoupper($otherLocale)])
                );
            }
        });
    }
}
