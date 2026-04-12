<?php

namespace App\Support\DataTables;

use Illuminate\Database\Eloquent\Builder;

/**
 * Fast, index-friendly search on user first/last name and full-name concat for Yajra DataTables.
 */
final class UserNameSearch
{
    public static function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    public static function applyToUserQuery(Builder $query, string $keyword): void
    {
        $like = '%'.self::escapeLike($keyword).'%';
        $query->where(function (Builder $q) use ($like) {
            $q->where('first_name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhereRaw("CONCAT(COALESCE(first_name,''), ' ', COALESCE(last_name,'')) LIKE ?", [$like]);
        });
    }

    public static function applyToUserRelation(Builder $query, string $keyword, string $relation = 'user'): void
    {
        $query->whereHas($relation, function (Builder $q) use ($keyword) {
            self::applyToUserQuery($q, $keyword);
        });
    }

    /**
     * Invoice list: name column (membership member vs merchandise customer).
     */
    public static function applyForInvoicePersonName(Builder $query, string $keyword): void
    {
        $query->where(function (Builder $q) use ($keyword) {
            $q->where('invoice_source', 'merchandise')
                ->whereHas('customer', function (Builder $u) use ($keyword) {
                    self::applyToUserQuery($u, $keyword);
                });
        })->orWhere(function (Builder $q) use ($keyword) {
            $q->where(function (Builder $q2) {
                $q2->whereNull('invoice_source')->orWhere('invoice_source', '!=', 'merchandise');
            })->whereHas('membership.user', function (Builder $u) use ($keyword) {
                self::applyToUserQuery($u, $keyword);
            });
        });
    }

    /**
     * Payment list / revenue: payer name from invoice + membership/customer (same rules as invoice UI).
     */
    public static function applyForPaymentPersonName(Builder $query, string $keyword): void
    {
        $query->where(function (Builder $q) use ($keyword) {
            $q->whereHas('invoice', function (Builder $inv) use ($keyword) {
                $inv->where('invoice_source', 'merchandise')
                    ->whereHas('customer', function (Builder $u) use ($keyword) {
                        self::applyToUserQuery($u, $keyword);
                    });
            });
        })->orWhere(function (Builder $q) use ($keyword) {
            $q->whereHas('invoice', function (Builder $inv) {
                $inv->where(function (Builder $q2) {
                    $q2->whereNull('invoice_source')->orWhere('invoice_source', '!=', 'merchandise');
                });
            })->whereHas('membership.user', function (Builder $u) use ($keyword) {
                self::applyToUserQuery($u, $keyword);
            });
        });
    }

    /**
     * Invoice "package" column: package name, custom membership, or merchandise.
     */
    public static function applyForInvoicePackageDisplay(Builder $query, string $keyword): void
    {
        $kw = '%'.self::escapeLike($keyword).'%';
        $lower = strtolower(trim($keyword));

        $query->where(function (Builder $q) use ($kw, $lower) {
            $q->whereHas('membership.package', fn (Builder $pq) => $pq->where('name', 'like', $kw));

            if ($lower !== '' && str_contains($lower, 'custom')) {
                $q->orWhere(function (Builder $q2) {
                    $q2->whereHas('membership', fn (Builder $m) => $m->whereNull('package_id'))
                        ->where(function (Builder $q3) {
                            $q3->whereNull('invoice_source')->orWhere('invoice_source', '!=', 'merchandise');
                        });
                });
            }

            if ($lower !== '' && str_contains('merchandise', $lower)) {
                $q->orWhere('invoice_source', 'merchandise');
            }
        });
    }
}
