<?php

namespace App\Http\Requests\Frontend;

use App\Services\SanitizationService;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /*
        | Any authenticated user can post a comment.
        | The route already requires auth middleware.
        | No permission check needed here —
        | readers, authors, editors, admins can all comment.
        */
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'content' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.required' => 'Comment cannot be empty.',
            'content.min'      => 'Comment must be at least 3 characters.',
            'content.max'      => 'Comment cannot exceed 1000 characters.',
            'post_id.exists'   => 'The post you are commenting on does not exist.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $sanitizer = app(SanitizationService::class);

        $this->merge([
            /*
            | Comments allow minimal HTML (bold, italic, links, code).
            | We use cleanComment() not cleanText() so readers can still
            | format their comments slightly.
            |
            | If you prefer comments to be plain text only, use cleanText().
            */
            'post_content' => $sanitizer->cleanComment($this->content),
        ]);
    }
}
