<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\StoreReadingPlanRequest;
use App\Http\Requests\UpdateReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画一覧画面を表示
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->input('status', '');

        $readingPlans = ReadingPlan::query()
            ->with('book')
            ->where('user_id', $request->user()->id)
            ->when(
                ReadingPlanStatus::tryFrom($currentStatus),
                fn ($query) => $query->where('status', $currentStatus)
            )
            ->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 読書計画作成画面を表示
     */
    public function create(): View
    {
        $books = Book::all();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 読書計画を登録
     */
    public function store(StoreReadingPlanRequest $request): RedirectResponse
    {
        ReadingPlan::create([
            'user_id' => auth()->id(),
            'book_id' => $request->validated('book_id'),
            'target_date' => $request->validated('target_date'),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を作成しました。');
    }

    /**
     * 読書計画編集画面を表示
     */
    public function edit(ReadingPlan $plan): View|RedirectResponse
    {
        $this->authorize('update', $plan);

        if ($plan->status === ReadingPlanStatus::Completed) {
            return redirect()->route('reading-plans.index');
        }

        return view('reading-plans.edit', ['readingPlan' => $plan]);
    }

    /**
     * 読書計画を更新
     */
    public function update(UpdateReadingPlanRequest $request, ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        if ($plan->status === ReadingPlanStatus::Completed) {
            return redirect()->route('reading-plans.index');
        }

        $plan->update([
            'target_date' => $request->validated('target_date'),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を更新しました。');
    }

    /**
     * 読書計画を削除
     */
    public function destroy(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('delete', $plan);

        $plan->delete();

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を削除しました。');
    }

    /**
     * 読書計画ステータスを完了に変更
     */
    public function complete(ReadingPlan $plan): RedirectResponse
    {
        $this->authorize('update', $plan);

        if ($plan->status === ReadingPlanStatus::Completed) {
            return redirect()->route('reading-plans.index');
        }

        $plan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()
            ->route('reading-plans.index')
            ->with('success', '読書計画を完了しました。');
    }
}
