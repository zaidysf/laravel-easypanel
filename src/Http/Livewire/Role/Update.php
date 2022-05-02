<?php

namespace EasyPanel\Http\Livewire\Role;

use Iya30n\DynamicAcl\ACL;
use Iya30n\DynamicAcl\Models\Role;
use Livewire\Component;

class Update extends Component
{
    public $role;

    public $name;

    public $permissionsData = [];

    public $access = [];

    public $selectedAll = [];

    protected $rules = [
        'name' => 'min:3'
    ];

    public function mount(Role $role)
    {
        $this->role = $role;

        $this->name = $role->name;

        $this->access = $this->replaceArrayKeys($role->permissions, '.', '-');
    }

    /** 
     * this method checks if whole checkboxes checked, set value true for SelectAll checkbox
     * 
     * @param string $key
     * 
     * @param string $dashKey
     */
    public function checkSelectedAll($key, $dashKey)
    {
        $selectedRoutes = array_filter($this->access[$dashKey]);

        // we don't have delete route in cruds but we have a button for it. that's why i added 1
        if(count($selectedRoutes) == count($this->permissionsData[$key]) + 1)
            $this->selectedAll[$dashKey] = true;
        else
            unset($this->selectedAll[$dashKey]);
    }

    public function updated($input)
    {
        $this->validateOnly($input);
    }

    public function update()
    {
        if ($this->getRules())
            $this->validate();

        if ($this->role->is_super_admin()) {
            $this->dispatchBrowserEvent('show-message', ['type' => 'error', 'message' => __('CannotUpdateMessage', ['name' => __('Role')])]);
            return;
        }

        $this->role->update([
            'name' => $this->name,
            'permissions' => $this->replaceArrayKeys($this->access, '-', '.')
        ]);

        $this->dispatchBrowserEvent('show-message', ['type' => 'success', 'message' => __('UpdatedMessage', ['name' => __('Role')])]);
    }

    public function render()
    {
        $permissions = ACL::getRoutes();

        $this->permissionsData = $permissions;

        return view('admin::livewire.role.update', [
            'role' => $this->role,
            'permissions' => $permissions,
        ])->layout('admin::layouts.app', ['title' => __('UpdateTitle', ['name' => __('Role')])]);
    }

    private function replaceArrayKeys($permissions, $from, $to)
    {
        foreach($permissions as $key => $value) {
            unset($permissions[$key]);
            $key = str_replace($from, $to, $key);
            $value = is_array($value) ? array_filter($value) : $value;

            if (empty($value))
                continue;

            $permissions[$key] = $value;
        }

        return $permissions;
    }
}
