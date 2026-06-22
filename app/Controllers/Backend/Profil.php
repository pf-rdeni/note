<?php

namespace App\Controllers\Backend;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Profil extends BaseController
{
    public function index()
    {
        $data = [
            'pageTitle' => 'Profil Akun',
            'user'      => user(),
        ];

        return view('backend/profil/index', $data);
    }

    public function update()
    {
        $userModel = model(UserModel::class);
        $user = user(); // Current logged in user entity

        // Validation rules
        $rules = [
            'fullname' => 'permit_empty|min_length[3]|max_length[100]',
            'avatar'   => 'permit_empty|is_image[avatar]|max_size[avatar,2048]|ext_in[avatar,jpg,jpeg,png,webp]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Update fullname
        $user->fullname = $this->request->getPost('fullname');

        // Handle avatar upload
        $avatarFile = $this->request->getFile('avatar');
        if ($avatarFile && $avatarFile->isValid() && ! $avatarFile->hasMoved()) {
            // New random name
            $newName = $avatarFile->getRandomName();
            
            // Move file to public/uploads/avatars
            $avatarFile->move(FCPATH . 'uploads/avatars', $newName);

            // Delete old avatar file if it exists and is local
            if (! empty($user->user_image)) {
                $oldImagePath = FCPATH . $user->user_image;
                if (file_exists($oldImagePath) && is_file($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            // Save relative path to DB
            $user->user_image = 'uploads/avatars/' . $newName;
        }

        // Save back to DB if there are changes
        if (! $user->hasChanged()) {
            return redirect()->back()->with('success', 'Profil berhasil diperbarui (tidak ada perubahan).');
        }

        if ($userModel->save($user)) {
            return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }
    }

    public function updatePassword()
    {
        $userModel = model(UserModel::class);
        $user = user();

        // Validation rules
        $rules = [
            'current_password' => 'required',
            'password'         => 'required|strong_password',
            'pass_confirm'     => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Verify current password
        $currentPassword = $this->request->getPost('current_password');
        if (! \Myth\Auth\Password::verify($currentPassword, $user->password_hash)) {
            return redirect()->back()->withInput()->with('error', 'Password saat ini salah.');
        }

        // Update password (triggers setter in User entity)
        $user->password = $this->request->getPost('password');

        if ($userModel->save($user)) {
            return redirect()->back()->with('success', 'Password berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $userModel->errors());
        }
    }
}
