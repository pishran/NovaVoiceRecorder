<?php

namespace Pishran\NovaVoiceRecorder;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaVoiceRecorder extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-voice-recorder';

    protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute): void
    {
        if ($request->exists($requestAttribute)) {
            tap($request->input($requestAttribute), function ($value) use ($model, $attribute) {
                $value = $this->isValidNullValue($value) ? null : $value;
                if ($value) {
                    $filename = Str::random(32).'.mp3';
                    $data = str($value)->after('data:audio/mp3;base64,')->fromBase64();

                    Storage::disk('voices')->put($filename, $data);

                    $this->fillModelWithData($model, $filename, $attribute);
                }
            });
        }
    }
}
