<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Script;

class Scripts extends Component
{
    public $isOpen = false;
    public $scripts;
    public $scriptid, $name, $script_type, $bg, $priority, $script;
    const DEFAULT_SCRIPT = "#!/bin/sh\nset -e\n\n";

    public function render()
    {
        $this->scripts = Script::orderBy('name')->get();
        return view('livewire.scripts');
    }

    public function toggleModal()
    {
        $this->isOpen = !$this->isOpen;
    }

    public function delete($id)
    {
        $script = Script::find($id);
        if (!$script) {
            session()->flash('message', 'Script not found.');
            return;
        }

        $projects = $script->projects;
        if (count($projects))
        {
            $plist = '';
            foreach ($projects as $project)
            {
                $plist .= "'".$project->name."' ";
            }
            session()->flash('message', 'Cannot delete. Script is being used by projects: '.$plist);
            return;
        }

        $script->delete();
        session()->flash('message', 'Script deleted.');
    }

    public function create()
    {
        $this->resetInputs();
        $this->script_type = 'postinstall';
        $this->bg = false;
        $this->priority = 100;
        $this->script = self::DEFAULT_SCRIPT;
        $this->toggleModal();
    }

    public function edit($id)
    {
        $script = Script::find($id);
        if (!$script) {
            session()->flash('message', 'Script not found.');
            return;
        }

        $this->scriptid = $id;
        $this->name = $script->name;
        $this->script_type = $script->script_type;
        $this->bg = $script->bg;
        $this->priority = $script->priority;
        $this->script = $script->script;
        $this->toggleModal();
    }

    public function store()
    {
        $this->validateData();

        Script::updateOrCreate(['id' => $this->scriptid], [
            'name' => $this->name,
            'script_type' => $this->script_type,
            'bg' => $this->bg,
            'priority' => $this->priority,
            'script' => str_replace("\r", "", $this->script)
        ]);

        $this->toggleModal();
    }

    public function cancel()
    {
        $this->toggleModal();
    }

    private function resetInputs()
    {
        $this->name = null;
        $this->scriptid = null;
    }

    private function validateData()
    {
        return $this->validate([
            'name' => 'required|max:255',
            'script_type' => 'in:preinstall,postinstall',
            'bg' => 'required|boolean',
            'priority' => 'required|numeric',
            'script' => 'required'
        ]);
    }
}
