<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEvent extends ViewRecord
{
    protected static string $resource = EventResource::class;

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Overview';
    }

    public function getContentTabIcon(): ?string
    {
        return 'heroicon-o-information-circle';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('view_public')
                ->label('View Public Page')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(fn () => route('public.events.show', $this->record->slug))
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->slug),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Event Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('title'),
                        Infolists\Components\TextEntry::make('slug'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('short_description')
                            ->columnSpanFull()
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('description')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('--'),
                    ])->columns(3),

                Infolists\Components\Section::make('Date & Time')
                    ->schema([
                        Infolists\Components\TextEntry::make('start_date')
                            ->dateTime('M j, Y g:i A'),
                        Infolists\Components\TextEntry::make('end_date')
                            ->dateTime('M j, Y g:i A'),
                        Infolists\Components\TextEntry::make('timezone'),
                        Infolists\Components\TextEntry::make('max_attendees')
                            ->label('Capacity')
                            ->placeholder('Unlimited'),
                        Infolists\Components\TextEntry::make('registrations_count')
                            ->state(fn ($record) => $record->registrations()->count())
                            ->label('Registrations'),
                        Infolists\Components\TextEntry::make('spots_remaining')
                            ->state(fn ($record) => $record->spotsRemaining() ?? 'Unlimited')
                            ->label('Spots Remaining'),
                    ])->columns(3),

                Infolists\Components\Section::make('Registration Window')
                    ->schema([
                        Infolists\Components\TextEntry::make('registration_start_date')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Opens immediately'),
                        Infolists\Components\TextEntry::make('registration_end_date')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Closes at event start'),
                        Infolists\Components\TextEntry::make('registration_status')
                            ->state(fn ($record) => $record->isRegistrationOpen() ? 'Open' : 'Closed')
                            ->badge()
                            ->color(fn ($state) => $state === 'Open' ? 'success' : 'danger'),
                    ])->columns(3),

                Infolists\Components\Section::make('Location')
                    ->schema([
                        Infolists\Components\TextEntry::make('location_name')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('location_address')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('city')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('state')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('zip')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('country')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('virtual_link')
                            ->url(fn ($state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('--')
                            ->columnSpanFull(),
                    ])->columns(3),

                Infolists\Components\Section::make('Contact & Display')
                    ->schema([
                        Infolists\Components\TextEntry::make('organizer_name')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('contact_email')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('contact_phone')
                            ->placeholder('--'),
                        Infolists\Components\IconEntry::make('is_featured')
                            ->boolean()
                            ->label('Featured'),
                        Infolists\Components\ImageEntry::make('featured_image_path')
                            ->label('Featured Image')
                            ->disk('public')
                            ->placeholder('No image'),
                    ])->columns(3),

                Infolists\Components\Section::make('Confirmation')
                    ->schema([
                        Infolists\Components\TextEntry::make('confirmation_message')
                            ->html()
                            ->columnSpanFull()
                            ->placeholder('Default confirmation message'),
                        Infolists\Components\TextEntry::make('confirmation_email_body')
                            ->columnSpanFull()
                            ->placeholder('Default email body'),
                    ])
                    ->collapsed(),

                Infolists\Components\Section::make('Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('createdBy.full_name')
                            ->label('Created By')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('published_at')
                            ->dateTime()
                            ->placeholder('Not published'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->dateTime(),
                        Infolists\Components\TextEntry::make('updated_at')
                            ->dateTime(),
                    ])->columns(4),
            ]);
    }
}
