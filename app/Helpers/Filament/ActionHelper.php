<?php

namespace App\Helpers\Filament;

use Closure;
use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Log;

class ActionHelper
{
    public static function makeSlideOver(
        string $name,
        array $form,
        string $modalHeading,
        string $label = 'Editar',
        ?callable $fillForm = null,
        ?callable $action = null,
        ?callable $before= null,
        ?callable $after = null,
        bool|callable $visible = null
    ): Action {
        $isEdit = $label === 'Editar';

        $actionBuilder = Action::make($name)
            ->form($form)
            ->model('formData')
            ->modalHeading($modalHeading)
            ->modalButton($isEdit ? 'Salvar alterações' : 'Salvar')
            ->icon($isEdit ? 'heroicon-c-pencil-square' : 'heroicon-m-plus-circle')
            ->label($label)
            ->action($action ?? function (array $data, $record) {
                Log::info('Editando conta', ['data' => $data]);
                foreach ($data as $key => $value) {
                    if (in_array($key, ['limit', 'amount', 'balance'])) {
                        // Permite números, vírgula e sinal negativo
                        $newValue = preg_replace('/[^0-9,\-]/', '', $value);

                        // Substitui vírgula por ponto e converte para float
                        $data[$key] = (float) str_replace(',', '.', $newValue);
                    }
                }


                $record->update($data);

                Notification::make()
                    ->title('Registro atualizado com sucesso!')
                    ->success()
                    ->send();
            })
            ->before($before)
            ->after($after)
            ->modalIcon($isEdit ? 'heroicon-c-pencil-square' : 'heroicon-m-plus-circle')
            ->slideOver(true);

        if ($isEdit && $fillForm !== false) {
            $actionBuilder->fillForm($fillForm ?? fn ($record) => $record->toArray());
        }


        if($visible) {
            $actionBuilder->visible($visible);
        }

        return $actionBuilder;
    }

}
