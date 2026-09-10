<?php

namespace App\Enums;

enum LoanStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case BORROWED = 'borrowed';
    case OVERDUE = 'overdue';
    case RETURNED = 'returned';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';
    case LOST = 'lost';
}
