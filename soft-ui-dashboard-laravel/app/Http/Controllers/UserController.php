<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use File;
class UserController extends Controller
{
    // app/Http/Controllers/MemberController.php
    public function index()
{
    $members = Member::where('id', Auth::member()->id)->simplePaginate(5);// 10 items per page

    return view('laravel-examples.user-management', compact('members'));
}
public function store(Request $request)
    {
        $validatedData = $request->validate([
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255', 
            'role' => 'required|string|max:255',
        ]);
        $image=$request->file('image');

        if ($request->hasFile('image')) {
            $filename = $image->getClientOriginalName();
            $uploadpath = 'uploads' . '/' . 'members-image';
            $image->move($uploadpath, $filename);
            $validatedData['image'] = $filename;
        }

        Member::create($validatedData);

        return redirect()->back()->with('success', 'User added successfully');
    }

    public function show(){
        $members=Member::all();
        return view('laravel-examples.user-management',['members' => $members]);
    }

    public function edit($id){
        $member=Member::findOrFail($id);
        return view('laravel-examples.edit', ['member' => $member]);
    }

    public function update(Request $request, $id)
    {
        
        $validatedData = $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255', 
            'role' => 'required|string|max:255',
        ]);

        $image = $request->file('image');
        $user = Member::find($id);
        
    
        if ($user) {
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($user->image) {
                    $oldImagePath = public_path('uploads/members-image/' . $user->image);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
    
                // Upload new image
                $filename = $image->getClientOriginalName();
                $uploadPath = 'uploads' . '/' . 'members-image';
                $image->move($uploadPath, $filename);
                $validatedData['image'] = $filename;
            }
    
            $user->update($validatedData);
    
            return redirect()->back()->with('success', 'User updated successfully');
        } else {
            return redirect()->back()->with('error', 'User not found');
        }
    }
    
    public function destroy($id)
    {
        $member = Member::findOrFail($id);
        if ($member != '') {
            $imagePath = public_path('uploads/members-image/' . $member->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
            $member->delete();
           return redirect()->back()->with('success','user has deleted');
        }
    }

}
