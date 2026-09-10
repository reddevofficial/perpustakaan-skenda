<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Student;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, ReportService $reports): View
    {
        $this->authorizeAccess();
        $filters = $this->filters($request);
        $type = $filters['type'];
        $query = $this->queryFor($reports, $type, $filters);

        return view('reports.index', [
            'type' => $type,
            'filters' => $filters,
            'records' => $query->paginate(25)->withQueryString(),
            'categories' => Category::query()->where('is_active', true)->orderBy('name')->get(),
            'classes' => Student::query()->whereNotNull('class')->distinct()->orderBy('class')->pluck('class'),
            'headers' => $reports->headers($type),
        ]);
    }

    public function export(Request $request, ReportService $reports): StreamedResponse
    {
        $this->authorizeAccess();
        $filters = $this->filters($request);
        $type = $filters['type'];
        $records = $this->queryFor($reports, $type, $filters)->get();
        $rows = $reports->rows($type, $records);
        $headers = $reports->headers($type);
        $filename = 'laporan-'.$type.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function queryFor(ReportService $reports, string $type, array $filters)
    {
        return match ($type) {
            'loans' => $reports->loans($filters),
            'fines' => $reports->fines($filters),
            'reservations' => $reports->reservations($filters),
            'inventory' => $reports->inventory($filters),
            'books' => $reports->books($filters),
            default => $reports->loans($filters),
        };
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'type' => ['nullable', 'in:loans,fines,reservations,inventory,books'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'status' => ['nullable', 'string', 'max:30'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'class' => ['nullable', 'string', 'max:50'],
        ]) + ['type' => $request->input('type', 'loans')];
    }

    private function authorizeAccess(): void
    {
        abort_unless(in_array(Auth::user()?->role?->value ?? Auth::user()?->role, ['staff', 'super_admin'], true), 403);
    }
}
