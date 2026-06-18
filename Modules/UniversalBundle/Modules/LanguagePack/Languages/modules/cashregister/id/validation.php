<?php 
return [
  'denomination' => [
    'name' => [
      'required' => 'Nama denominasi wajib diisi.',
      'max' => 'Nama denominasi tidak boleh lebih dari :max karakter.',
    ],
    'value' => [
      'required' => 'Nilai denominasi diperlukan.',
      'numeric' => 'Nilai pecahannya harus berupa angka.',
      'min' => 'Nilai pecahannya minimal harus :min.',
      'max' => 'Nilai pecahannya tidak boleh lebih besar dari :max.',
    ],
    'type' => [
      'required' => 'Jenis denominasi wajib diisi.',
      'in' => 'Jenis denominasi yang dipilih tidak valid.',
    ],
    'description' => [
      'max' => 'Deskripsi denominasi tidak boleh lebih dari :max karakter.',
    ],
  ],
];