<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Broadcast;
use App\Models\Celebration;
use App\Models\Policy;
use App\Models\StaticPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PreviewController extends Controller
{
    /**
     * Map of short model names to their fully qualified class names.
     *
     * @var array<string, class-string<Model>>
     */
    protected array $modelMap = [
        'broadcast' => Broadcast::class,
        'achievement' => Achievement::class,
        'celebration' => Celebration::class,
        'policy' => Policy::class,
        'static-page' => StaticPage::class,
    ];

    /**
     * Display a preview of a content record (draft or published).
     */
    public function show(Request $request, string $model, int $id): View
    {
        $modelClass = $this->modelMap[$model] ?? null;

        if (! $modelClass) {
            throw new NotFoundHttpException(__('common.preview_model_not_found'));
        }

        $record = $modelClass::query()->findOrFail($id);

        return view('preview.show', [
            'record' => $record,
            'modelType' => $model,
            'isPreview' => true,
        ]);
    }
}
