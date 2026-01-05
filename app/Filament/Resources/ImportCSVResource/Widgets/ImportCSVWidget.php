<?php

namespace App\Filament\Resources\ImportCSVResource\Widgets;

use App\Jobs\ImportJob;
use App\Models\Import;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class ImportCSVWidget extends Widget implements HasActions, HasForms
{
    use InteractsWithActions, InteractsWithForms;

    protected static string $view = 'filament.resources.import-c-s-v-resource.widgets.import-c-s-v-widget';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return (bool) ($user && method_exists($user, 'isAdmin') && $user->isAdmin());
    }

    public function uploadCsvAction(): Action
    {
        return Action::make('uploadCsv')
            ->label('Upload CSV')
            ->icon('heroicon-o-arrow-up-tray')
            ->visible(fn () => static::canView())
            ->authorize(fn () => static::canView())
            ->form([
                TextInput::make('period')
                    ->label('Period (YYYY-MM)')
                    ->default(now()->format('Y-m'))
                    ->required(),

                FileUpload::make('file')
                    ->label('CSV file')
                    ->disk('import')
                    // ->name('hello.csv')
                    ->acceptedFileTypes(['text/csv', 'text/plain', '.csv'])
                    ->storeFiles(false)
                    ->required(),
            ])
            ->action(function (array $data) {
                abort_unless(static::canView(), 403);

                /** @var TemporaryUploadedFile|string $file */
                $file = $data['file'];

                $filename = 'Import_'.$data['period'].'_'.Carbon::now()->timestamp.'.csv';
                $storedPath = $file->storeAs('', $filename, 'import'); // disk=imports
                $import = Import::factory([
                    'period' => $data['period'],
                    'file_path' => $storedPath,
                ])->create();
                ImportJob::dispatch($import);
                // $storedPath = is_string($file) ? $file : $file->store('imports/uploads', 'local');

                Notification::make()
                    ->title('Import queued')
                    ->body("Import #{$import->id} created for {$import->period}")
                    ->success()
                    ->send();
            });
    }
}
