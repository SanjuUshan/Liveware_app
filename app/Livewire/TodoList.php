<?php

namespace App\Livewire;

use App\Models\Todo;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use PhpParser\Node\Stmt\TryCatch;
use Exception;

class TodoList extends Component
{

    use WithPagination;

    // #[Rule('required|min:3|max:50')]
    public $name;

    public $search;

    public $editingTodoID;
    public $editingNewName;

    public function create(){
        /* 1. validtion
           2. create the todo
           3. clear the input
           4. send flash message  */


        //    $this->validateOnly('name'); //we can use theis validation type

           $validated = $this->validate([
            'name' => 'required|min:3|max:50',
           ]);

           Todo::create($validated);

           $this->reset('name');
           session()->flash('success','Task Created Successfully!');

           $this->resetPage();

    }

    public function delete($todoId){


        try {
            Todo::findOrfail($todoId)->delete();
        } catch (Exception $th) {
            session()->flash('error','Failed to delete todo !');
            return;
        }

    }

    // public function delete(Todo $todo){
    //     $todo->delete();
    // }// <- this is another way to delete

    public function toggle($todoId){
        $todo=Todo::find($todoId);
        $todo->completed = !$todo->completed;
        $todo->save();
    }

    public function edit($todoId){
        $this->editingTodoID=$todoId;
        $this->editingNewName = Todo::find($todoId)->name;
    }

    public function cancelEdit(){
        $this->reset('editingTodoID','editingNewName');
    }

    public function update(){
        $validated = $this->validate([
            'editingNewName' => 'required|min:3|max:50',
           ]);
        Todo::find($this->editingTodoID)->update($validated,[
            'name' => $this->editingNewName
        ]);

        $this->cancelEdit();
    }

    public function render()
    {
        return view('livewire.todo-list',[
            'savedTodo' => Todo::latest()
            ->where('name','like',"%{$this->search}%")
            ->paginate(5)
        ]);
    }
}
