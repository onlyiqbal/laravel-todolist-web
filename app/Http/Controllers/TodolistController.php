<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use App\Services\TodolistService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TodolistController extends Controller
{

    private TodolistService $todolistService;

    public function __construct(TodolistService $todolistService)
    {
        $this->todolistService = $todolistService;
    }

    public function todoList(Request $request)
    {
        $todolist = $this->todolistService->getTodolist();
        $tanggal = Carbon::parse($todolist[0]['created_at'])->format('d F Y');
        return response()->view("todolist.todolist", [
            "title" => "Todolist",
            "todolist" => $todolist,
            "tanggal" => $tanggal
        ]);
    }

    public function addTodo(Request $request)
    {
        $todo = $request->input("todo");

        if (empty($todo)) {
            $todolist = $this->todolistService->getTodolist();
            return response()->view("todolist.todolist", [
                "title" => "Todolist",
                "todolist" => $todolist,
                "error" => "Todo is required"
            ]);
        }

        $this->todolistService->saveTodo(uniqid(), $todo);

        return redirect()->action([TodolistController::class, 'todoList']);
    }

    public function editTodo($id)
    {
        $todo = $this->todolistService->getTodoById($id);
        return response()->view('todolist.edit', [
            'todo' => $todo
        ]);
    }

    public function updateTodo(Request $request)
    {
        $this->validate($request, [
            'id' => 'required',
            'todo' => 'required'
        ]);

        $this->todolistService->updateTodo($request->input('id'), $request->input('todo'));

        $todolist = $this->todolistService->getTodolist();
        $tanggal = Carbon::parse($todolist[0]['updated_at'])->format('d F Y');
        return response()->view('todolist.todolist', [
            'title' => 'Todolist',
            'todolist' => $this->todolistService->getTodolist(),
            'tanggal' => $tanggal
        ]);
    }

    public function removeTodo(Request $request, string $todoId): RedirectResponse
    {
        $this->todolistService->removeTodo($todoId);
        return redirect()->action([TodolistController::class, 'todoList']);
    }
}
