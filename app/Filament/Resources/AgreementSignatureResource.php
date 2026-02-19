<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AgreementSignatureResource\Pages;
use App\Models\AgreementSignature;
use App\Models\Plan;
use App\Models\Training;
use App\Services\DisputeEvidenceService;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgreementSignatureResource extends Resource
{
    protected static ?string $model = AgreementSignature::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 11;

    protected static ?string $navigationLabel = 'Consent Signatures';

    protected static ?string $modelLabel = 'Consent Signature';

    protected static ?string $pluralModelLabel = 'Consent Signatures';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.email', 'user.first_name', 'user.last_name'];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['user', 'agreement']);
    }

    public static function getGlobalSearchResultTitle(\Illuminate\Database\Eloquent\Model $record): string
    {
        return ($record->user?->first_name ?? '') . ' ' . ($record->user?->last_name ?? '') . ' — ' . ($record->agreement?->title ?? '');
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'User' => $record->user?->email,
            'Agreement' => $record->agreement?->title,
        ];
    }

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
                Tables\Columns\TextColumn::make('context_reference')
                    ->label('Reference')
                    ->state(function (AgreementSignature $record): string {
                        if (! $record->context_reference_type || ! $record->context_reference_id) {
                            return '—';
                        }

                        $model = $record->context_reference_type::find($record->context_reference_id);

                        if (! $model) {
                            return 'Deleted (#' . $record->context_reference_id . ')';
                        }

                        return match ($record->context_reference_type) {
                            'App\Models\Plan' => $model->name,
                            'App\Models\Training' => $model->title,
                            default => '#' . $record->context_reference_id,
                        };
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereIn('context_reference_id', Plan::where('name', 'like', "%{$search}%")->pluck('id'))
                                ->where('context_reference_type', 'App\\Models\\Plan');
                        })->orWhere(function ($q) use ($search) {
                            $q->whereIn('context_reference_id', Training::where('title', 'like', "%{$search}%")->pluck('id'))
                                ->where('context_reference_type', 'App\\Models\\Training');
                        });
                    }),
                Tables\Columns\TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? '$' . number_format($state / 100, 2) : '—')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('signed_at')
                    ->label('Signed At')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stripe_transaction_id')
                    ->label('Stripe Txn')
                    ->limit(20)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Filters\SelectFilter::make('training')
                    ->label('Training')
                    ->options(fn () => Training::orderBy('start_date', 'desc')->pluck('title', 'id'))
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->where('context_reference_type', 'App\Models\Training')
                            ->where('context_reference_id', $data['value'])
                        : $query
                    ),
                Tables\Filters\SelectFilter::make('plan')
                    ->label('Plan')
                    ->options(fn () => Plan::orderBy('sort_order')->pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data) => $data['value']
                        ? $query->where('context_reference_type', 'App\Models\Plan')
                            ->where('context_reference_id', $data['value'])
                        : $query
                    ),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->modalHeading(fn (AgreementSignature $record) => 'Consent Signature #' . $record->id)
                        ->infolist([
                            Infolists\Components\Section::make('User Information')
                                ->schema([
                                    Infolists\Components\TextEntry::make('user.full_name')
                                        ->label('Name'),
                                    Infolists\Components\TextEntry::make('user.email')
                                        ->label('Email'),
                                    Infolists\Components\TextEntry::make('user_id')
                                        ->label('User ID'),
                                ])->columns(3),

                            Infolists\Components\Section::make('Consent Details')
                                ->schema([
                                    Infolists\Components\TextEntry::make('agreement.title')
                                        ->label('Agreement'),
                                    Infolists\Components\TextEntry::make('agreement.version')
                                        ->label('Version')
                                        ->badge(),
                                    Infolists\Components\TextEntry::make('signed_at')
                                        ->label('Signed At')
                                        ->dateTime(),
                                    Infolists\Components\TextEntry::make('ip_address')
                                        ->label('IP Address'),
                                    Infolists\Components\TextEntry::make('user_agent')
                                        ->label('User Agent')
                                        ->columnSpanFull(),
                                    Infolists\Components\TextEntry::make('consent_context')
                                        ->label('Context')
                                        ->badge()
                                        ->color(fn (?string $state): string => match ($state) {
                                            'membership_subscription' => 'success',
                                            'plan_switch' => 'info',
                                            'training_registration' => 'warning',
                                            'trainer_application' => 'danger',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn (?string $state): string => match ($state) {
                                            'membership_subscription' => 'Membership Subscription',
                                            'plan_switch' => 'Plan Switch',
                                            'training_registration' => 'Training Registration',
                                            'trainer_application' => 'Trainer Application',
                                            default => $state ?? 'N/A',
                                        }),
                                    Infolists\Components\TextEntry::make('context_reference_display')
                                        ->label('Reference')
                                        ->state(function (AgreementSignature $record): string {
                                            if (! $record->context_reference_type || ! $record->context_reference_id) {
                                                return 'N/A';
                                            }
                                            $model = $record->context_reference_type::find($record->context_reference_id);
                                            if (! $model) {
                                                return 'Deleted (#' . $record->context_reference_id . ')';
                                            }
                                            return match ($record->context_reference_type) {
                                                'App\Models\Plan' => $model->name,
                                                'App\Models\Training' => $model->title . ' (' . $model->start_date->format('M j, Y') . ')',
                                                default => '#' . $record->context_reference_id,
                                            };
                                        }),
                                    Infolists\Components\TextEntry::make('amount_cents')
                                        ->label('Amount')
                                        ->formatStateUsing(fn (?int $state): string => $state !== null ? '$' . number_format($state / 100, 2) : 'N/A'),
                                    Infolists\Components\TextEntry::make('stripe_transaction_id')
                                        ->label('Stripe Transaction ID')
                                        ->default('Pending'),
                                ])->columns(2),

                            Infolists\Components\Section::make('Terms & Conditions Snapshot')
                                ->schema([
                                    Infolists\Components\TextEntry::make('consent_snapshot')
                                        ->label('')
                                        ->html()
                                        ->columnSpanFull(),
                                ])
                                ->visible(fn (AgreementSignature $record): bool => $record->consent_snapshot !== null)
                                ->collapsible(),
                        ]),

                    Tables\Actions\Action::make('export_evidence')
                        ->label('Export Evidence PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('success')
                        ->visible(fn (AgreementSignature $record): bool => $record->consent_snapshot !== null)
                        ->action(function (AgreementSignature $record) {
                            $service = app(DisputeEvidenceService::class);
                            $pdf = $service->generate($record);

                            $userName = str($record->user?->full_name ?? 'unknown')->slug();
                            $context = str($record->agreement?->title ?? 'consent')->slug();

                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                "{$userName}-{$context}-evidence.pdf",
                                ['Content-Type' => 'application/pdf']
                            );
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAgreementSignatures::route('/'),
        ];
    }
}
