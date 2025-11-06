<?php

namespace App\Http\Livewire;

use App\Models\Link;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class LinksList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortBy = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortByColumn($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $links = Link::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('original_url', 'like', '%' . $this->search . '%')
                      ->orWhere('title', 'like', '%' . $this->search . '%')
                      ->orWhere('custom_alias', 'like', '%' . $this->search . '%')
                      ->orWhere('short_code', 'like', '%' . $this->search . '%');
                });
            })
            ->withCount('clicks')
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.links-list', compact('links'));
    }
}
?>
