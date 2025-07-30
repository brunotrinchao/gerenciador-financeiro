<?php

namespace App\Helpers\Filament;

use Filament\Forms\Components\Component;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Filament\Tables\Actions\Action;
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
    ): Action {
        $isEdit = $label === 'Editar';

        $actionBuilder = Action::make($name)
            ->form($form)
            ->model('formData')
            ->modalHeading($modalHeading)
            ->modalButton($isEdit ? 'Salvar alterações' : 'Salvar')
            ->label($label)
            ->icon($isEdit ? 'heroicon-m-pencil' : 'heroicon-m-plus')
            ->action($action ?? function (array $data, $record) {

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
            ->slideOver(true);

        if ($isEdit && $fillForm !== false) {
            $actionBuilder->fillForm($fillForm ?? fn ($record) => $record->toArray());
        }

        return $actionBuilder;
    }

}
