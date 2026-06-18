<?php 
return [
  'multiPOS' => [
    'name' => [
      'required' => 'Nama multi pos wajib diisi.',
      'max' => 'Nama multi pos tidak boleh lebih dari :max karakter.',
    ],
    'type' => [
      'required' => 'Jenis multi pos diperlukan.',
      'in' => 'Jenis multi pos yang dipilih tidak valid.',
    ],
  ],
];