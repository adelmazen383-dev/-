<?php

namespace App\Enums;

enum ContractStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case VIEWED = 'viewed';
    case SIGNED_BY_LESSEE = 'signed_by_lessee';
    case SIGNED = 'signed';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'مسودة',
            self::SENT => 'مُرسل',
            self::VIEWED => 'تمت المشاهدة',
            self::SIGNED_BY_LESSEE => 'موقّع من المستأجر',
            self::SIGNED => 'موقّع نهائياً',
            self::REJECTED => 'مرفوض',
            self::CANCELLED => 'ملغى',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'badge-draft',
            self::SENT => 'badge-sent',
            self::VIEWED => 'badge-viewed',
            self::SIGNED_BY_LESSEE => 'badge-lessee-signed',
            self::SIGNED => 'badge-signed',
            self::REJECTED => 'badge-rejected',
            self::CANCELLED => 'badge-cancelled',
        };
    }

    /**
     * States that are considered "terminal" — no further action allowed.
     */
    public static function terminalStates(): array
    {
        return [self::SIGNED, self::REJECTED, self::CANCELLED];
    }

    /**
     * States that are considered "pending" — awaiting client action.
     */
    public static function pendingStates(): array
    {
        return [self::DRAFT, self::SENT, self::VIEWED, self::SIGNED_BY_LESSEE];
    }

    public function isTerminal(): bool
    {
        return in_array($this, self::terminalStates());
    }
}
