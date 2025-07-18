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
        ?callable $after = null,
    ): Action {
        $isEdit = $label === 'Editar';

        $actionBuilder = Action::make($name)
            ->form($form)
            ->modalHeading($modalHeading)
            ->modalButton($isEdit ? 'Salvar alterações' : 'Salvar')
            ->label($label)
            ->icon($isEdit ? 'heroicon-m-pencil' : 'heroicon-m-plus')
            ->action($action ?? function (array $data, $record) {
                foreach ($data as $key => $value) {
                    if (in_array($key, ['limit', 'amount'])) {
                        $limite = str_replace(['.', ','], ['', '.'], $data[$key]); // transforma "0,00" em 0.00
                        $data[$key] = (float)$limite === 0.0 ? 0 : $limite;
                    }
                }

                $record->update($data);

                Notification::make()
                    ->title('Registro atualizado com sucesso!')
                    ->success()
                    ->send();
            })
            ->after($after)
            ->slideOver(true);

        if ($isEdit && $fillForm !== false) {
            $actionBuilder->fillForm($fillForm ?? fn ($record) => $record->toArray());
        }

        return $actionBuilder;
    }

}
