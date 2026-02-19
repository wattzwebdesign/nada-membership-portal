<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgreementSignatureResource\Pages;
use App\Models\AgreementSignature;
use App\Services\DisputeEvidenceService;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AgreementSignatureResource extends Resource
{
    protected static ?string $model = AgreementSignature::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Consent Signatures';

    protected static ?string $modelLabel = 'Consent Signature';

    protected static ?string $pluralModelLabel = 'Consent Signatures';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('User')
                    ->description(fn (AgreementSignature $record): string => $record->user?->full_name ?? '')
                    ->searchable(['email', 'first_name', 'last_name']),
                Tables\Columns\TextColumn::make('agreement.title')
                    ->label('Agreement')
                    ->sortable(),
                Tables\Columns\TextColumn::make('agreement.version')
                    ->label('Version')
                    ->sortable(),
                Tables\Columns\TextColumn::make('consent_context')
                    ->label('Context')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'membership_subscription' => 'success',
                        'plan_switch' => 'info',
                        'training_registration' => 'warning',
                        'trainer_application' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('signed_at')
                    ->label('Signed At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('signed_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('agreement_slug')
                    ->label('Agreement Type')
                    ->options([
                        'nda' => 'NDA',
                        'terms-of-service' => 'Terms & Conditions',
                    ])
                    ->query(fn (Tables\Filters\SelectFilter $filter, $query) => $filter->getState()['value']
                        ? $query->whereHas('agreement', fn ($q) => $q->where('slug', $filter->getState()['value']))
                        : $query
                    ),
                Tables\Filters\SelectFilter::make('consent_context')
                    ->label('Consent Context')
                    ->options([
                        'membership_subscription' => 'Membership Subscription',
                        'plan_switch' => 'Plan Switch',
                        'training_registration' => 'Training Registration',
                        'trainer_application' => 'Trainer Application',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('export_evidence')
                    ->label('Export Evidence')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success')
                    ->visible(fn (AgreementSignature $record): bool => $record->consent_snapshot !== null)
                    ->action(function (AgreementSignature $record) {
                        $service = app(DisputeEvidenceService::class);
                        $pdf = $service->generate($record);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'consent-evidence-' . $record->id . '.pdf',
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgreementSignatures::route('/'),
        ];
    }
}
