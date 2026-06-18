<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

function updateLanguageFile(string $file, array $updates): void
{
    $values = file_exists($file) ? require $file : [];

    if (!is_array($values)) {
        $values = [];
    }

    foreach ($updates as $key => $value) {
        data_set($values, $key, $value);
    }

    $content = "<?php\n\nreturn " . var_export($values, true) . ";\n";

    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }

    file_put_contents($file, $content);
}

updateLanguageFile(lang_path('km/modules.php'), [
    'customer.phone' => 'លេខទូរស័ព្ទ',
    'customer.customer' => 'អតិថិជន',
    'customer.customerAddress' => 'អាសយដ្ឋានអតិថិជន',

    'menu.itemName' => 'ឈ្មោះមុខម្ហូប',

    'order.qty' => 'ចំនួន',
    'order.price' => 'តម្លៃ',
    'order.amount' => 'ចំនួនទឹកប្រាក់',
    'order.subTotal' => 'សរុបរង',
    'order.discount' => 'បញ្ចុះតម្លៃ',
    'order.tip' => 'ប្រាក់ធីប',
    'order.totalTax' => 'ពន្ធសរុប',
    'order.total' => 'សរុប',
    'order.balanceReturn' => 'ប្រាក់អាប់',
    'order.paymentStatus' => 'ស្ថានភាពការទូទាត់',
    'order.paid' => 'បានទូទាត់',
    'order.unpaid' => 'មិនទាន់ទូទាត់',
    'order.paymentDetails' => 'ព័ត៌មានការទូទាត់',
    'order.paymentMethod' => 'វិធីទូទាត់',
    'order.orderNumber' => 'លេខបញ្ជាទិញ',
    'order.tokenNumber' => 'លេខថូខិន',
    'order.noOfPax' => 'ចំនួនភ្ញៀវ',
    'order.waiter' => 'អ្នកបម្រើ',
    'order.note' => 'កំណត់ចំណាំ',
    'order.guest' => 'ភ្ញៀវ',
    'order.split' => 'ចែកវិក្កយបត្រ',
    'order.totalSplits' => 'ចំនួនការចែកសរុប',
    'order.dine_in' => 'ញ៉ាំនៅហាង',
    'order.pickup' => 'មកយកដោយខ្លួនឯង',
    'order.delivery' => 'ដឹកជញ្ជូន',

    'delivery.deliveryFee' => 'ថ្លៃដឹកជញ្ជូន',
    'delivery.freeDelivery' => 'ដឹកជញ្ជូនឥតគិតថ្លៃ',

    'settings.tableNumber' => 'លេខតុ',
    'settings.orderDetails' => 'ព័ត៌មានបញ្ជាទិញ',
    'settings.payFromYourPhone' => 'ទូទាត់តាមទូរស័ព្ទរបស់អ្នក',
    'settings.scanQrCode' => 'ស្កេនកូដ QR',
    'settings.branchCrNumber' => 'លេខចុះបញ្ជីសាខា',
    'settings.branchVatNumber' => 'លេខអាករលើតម្លៃបន្ថែមសាខា',
]);

updateLanguageFile(lang_path('km/messages.php'), [
    'thankYouVisit' => 'សូមអរគុណសម្រាប់ការអញ្ជើញមក!',
]);

updateLanguageFile(lang_path('km/app.php'), [
    'dateTime' => 'កាលបរិច្ឆេទ និងម៉ោង',
    'back' => 'ត្រឡប់ក្រោយ',
    'stampDiscount' => 'ការបញ្ចុះតម្លៃត្រា',
    'freeItem' => 'មុខម្ហូបឥតគិតថ្លៃ',
    'free' => 'ឥតគិតថ្លៃ',
]);

echo "Khmer receipt translations updated successfully.\n";
