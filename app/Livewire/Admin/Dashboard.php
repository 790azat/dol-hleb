<?php

namespace App\Livewire\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Dashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'orders';

    #[Url(as: 'q', except: '')]
    public string $search = '';

    public function mount(): void
    {
        abort_unless(session('admin'), 403);
    }

    public function updatedTab(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setStatus(int $orderId, string $status): void
    {
        abort_unless(array_key_exists($status, Order::STATUSES), 422);
        Order::whereKey($orderId)->update(['status' => $status]);
    }

    public function savePrice(int $productId, $price): void
    {
        $price = $price === '' || $price === null ? null : max(0, (float) str_replace([' ', ','], ['', '.'], (string) $price));
        Product::whereKey($productId)->update(['price' => $price]);
    }

    public function toggle(int $productId, string $field): void
    {
        abort_unless(in_array($field, ['is_visible', 'is_featured', 'is_new']), 422);
        $p = Product::findOrFail($productId);
        $p->update([$field => ! $p->{$field}]);
    }

    public function publishReview(int $id, bool $publish): void
    {
        Review::whereKey($id)->update(['is_published' => $publish]);
    }

    public function deleteReview(int $id): void
    {
        Review::whereKey($id)->delete();
    }

    public function logout()
    {
        session()->forget('admin');

        return $this->redirectRoute('home');
    }

    public function render()
    {
        $data = match ($this->tab) {
            'products' => ['products' => Product::when($this->search, fn ($q) => $q->where('name', $q->getConnection()->getDriverName() === 'pgsql' ? 'ilike' : 'like', "%{$this->search}%"))->orderBy('position')->paginate(30)],
            'reviews' => ['reviews' => Review::latest()->paginate(30)],
            default => ['orders' => Order::latest()->paginate(20)],
        };

        return view('livewire.admin.dashboard', $data + [
            'newOrders' => Order::where('status', 'new')->count(),
            'pendingReviews' => Review::where('is_published', false)->count(),
        ])->title('Админка');
    }
}
