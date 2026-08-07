<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PosCashEvent;
use App\Models\PosSession;
use App\Models\PosSetting;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class PosSessionController extends Controller
{
    public function index()
    {
        $sessions = PosSession::with(['user', 'warehouse'])
            ->latest('opened_at')
            ->paginate(20);

        $openSessions = PosSession::open()->with(['user', 'warehouse'])->latest('opened_at')->get();

        return view('tenant.pos.sessions.index', compact('sessions', 'openSessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opening_cash' => 'required|numeric|min:0',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $existing = PosSession::open()->where('user_id', auth()->id())->first();
        if ($existing) {
            return back()->with('error', 'আপনার একটি রেজিস্টার সেশন ইতিমধ্যে খোলা আছে।');
        }

        $warehouse = $validated['warehouse_id']
            ? Warehouse::find($validated['warehouse_id'])
            : (PosSetting::current()->default_warehouse_id
                ? Warehouse::find(PosSetting::current()->default_warehouse_id)
                : null);

        $warehouse ??= Warehouse::where('is_active', true)->first();

        PosSession::create([
            'user_id' => auth()->id(),
            'warehouse_id' => $warehouse?->id,
            'opened_at' => now(),
            'opening_cash' => $validated['opening_cash'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'open',
        ]);

        return back()->with('success', 'রেজিস্টার সেশন খোলা হয়েছে!');
    }

    public function show(PosSession $session)
    {
        $session->load(['user', 'warehouse', 'orders' => fn ($q) => $q->latest(), 'cashEvents']);

        $cashIn = $session->cashEvents->where('type', 'in')->sum('amount');
        $cashOut = $session->cashEvents->where('type', 'out')->sum('amount');

        $expectedCash = round((float) $session->opening_cash + (float) $session->cash_sales + $cashIn - $cashOut - (float) $session->refunds_total, 2);

        return view('tenant.pos.sessions.show', compact('session', 'cashIn', 'cashOut', 'expectedCash'));
    }

    public function close(PosSession $session, Request $request)
    {
        if ($session->status !== 'open') {
            return back()->with('error', 'এই সেশন ইতিমধ্যে বন্ধ।');
        }

        $validated = $request->validate([
            'closing_cash' => 'required|numeric|min:0',
        ]);

        $cashIn = $session->cashEvents()->where('type', 'in')->sum('amount');
        $cashOut = $session->cashEvents()->where('type', 'out')->sum('amount');

        $expectedCash = round(
            (float) $session->opening_cash + (float) $session->cash_sales + $cashIn - $cashOut - (float) $session->refunds_total,
            2
        );

        $session->update([
            'closing_cash' => $validated['closing_cash'],
            'expected_cash' => $expectedCash,
            'cash_difference' => round($validated['closing_cash'] - $expectedCash, 2),
            'closed_at' => now(),
            'status' => 'closed',
        ]);

        return back()->with('success', 'রেজিস্টার সেশন বন্ধ হয়েছে!');
    }

    public function cashEvent(PosSession $session, Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:in,out',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:500',
        ]);

        PosCashEvent::create([
            'pos_session_id' => $session->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'reason' => $validated['reason'] ?? null,
        ]);

        return back()->with('success', 'ক্যাশ এন্ট্রি যুক্ত হয়েছে!');
    }
}
