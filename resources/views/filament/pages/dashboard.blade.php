<x-filament::page>
    <div class="space-y-6">

        {{-- Filtros --}}
        <div class="filament-widgets-container grid gap-4 md:grid-cols-12">
                <x-filament::card>
                    {{ $this->form }}
                </x-filament::card>

        </div>

        {{-- Conteúdo do dashboard abaixo --}}
        <div class="filament-widgets-container grid gap-4 md:grid-cols-12">
            {{-- Linha 1 - 12 colunas --}}
            <x-filament::widget class="md:col-span-12" :widget="\App\Filament\Widgets\CountWidget::class" />

            {{-- Linha 2 - 8 + 4 colunas --}}
{{--            <x-filament::widget class="md:col-span-8" :widget="\App\Filament\Widgets\UpcomingTransactionsWidget::class" />--}}
{{--            <x-filament::widget class="md:col-span-4" :widget="\App\Filament\Widgets\PerCardChartWidget::class" />--}}

{{--            --}}{{-- Linha 3 - 8 + 4 colunas --}}
{{--            <x-filament::widget class="md:col-span-8" :widget="\App\Filament\Widgets\PerCategoryChartWidget::class" />--}}
{{--            <x-filament::widget class="md:col-span-4" :widget="\App\Filament\Widgets\CountChartWidget::class" />--}}
        </div>

    </div>
</x-filament::page>
