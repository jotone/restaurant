<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

interface CrudInterface
{
	// GET|HEAD page -> view all items
	public function index(Request $request);

	// GET|HEAD page -> create item
	public function create(Request $request);

	// GET|HEAD page -> view item by ID
	public function show($id, Request $request);

	// GET|HEAD page -> view editable item by ID
	public function edit($id, Request $request);

	// POST create item
	public function store(Request $request);

	// PUT|PATCH edit item
	public function update($id, Request $request);

	// DELETE drop item
	public function destroy($id);
}