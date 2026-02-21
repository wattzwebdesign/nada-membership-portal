<?php

namespace App\Filament\Resources\EventRegistrationResource\Pages;

use App\Filament\Resources\EventRegistrationResource;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewEventRegistration extends ViewRecord
{
    protected static string $resource = EventRegistrationResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Registration Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('registration_number'),
                        Infolists\Components\TextEntry::make('event.title'),
                        Infolists\Components\TextEntry::make('full_name')
                            ->label('Name'),
                        Infolists\Components\TextEntry::make('email'),
                        Infolists\Components\TextEntry::make('phone'),
                        Infolists\Components\TextEntry::make('status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('payment_status')
                            ->badge(),
                        Infolists\Components\TextEntry::make('total_formatted')
                            ->label('Total'),
                        Infolists\Components\IconEntry::make('is_member_pricing')
                            ->boolean()
                            ->label('Member Pricing'),
                    ])->columns(3),

                Infolists\Components\Section::make('Check-In')
                    ->schema([
                        Infolists\Components\TextEntry::make('checked_in_at')
                            ->dateTime()
                            ->placeholder('Not checked in'),
                        Infolists\Components\TextEntry::make('checkedInBy.full_name')
                            ->label('Checked In By')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('qr_code_token')
                            ->label('QR Token'),
                    ])->columns(3),

                Infolists\Components\Section::make('Payment')
                    ->schema([
                        Infolists\Components\TextEntry::make('stripe_checkout_session_id')
                            ->label('Stripe Session')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('stripe_payment_intent_id')
                            ->label('Payment Intent')
                            ->placeholder('--'),
                        Infolists\Components\TextEntry::make('invoice.number')
                            ->label('Invoice')
                            ->placeholder('--'),
                    ])->columns(3),

                Infolists\Components\Section::make('Form Responses')
                    ->schema([
                        Infolists\Components\KeyValueEntry::make('form_data')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->form_data)),

                Infolists\Components\Section::make('Notes')
                    ->schema([
                        Infolists\Components\TextEntry::make('notes')
                            ->label('')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => ! empty($record->notes)),
            ]);
    }
}
