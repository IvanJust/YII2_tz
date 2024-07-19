<?php

namespace app\controllers;

use app\models\Order;
use app\models\Seller;
use Yii;
use yii\web\Controller;
use yii\data\ActiveDataProvider;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderController extends Controller
{
    public function actionIndex()
    { 
        $model = new Order(); // Создание модели для формы
        $dataProvider = new ActiveDataProvider([
            'query' => Order::find()->joinWith('seller')->select(['orders.*', 'sellers.title']),
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'attributes' => [
                    'id',
                    'date_sold',
                    'seller.title',
                    'qty',
                    'order_sum',
                ],
                'defaultOrder' => [
                    'date_sold' => SORT_DESC,
                ],
            ],
        ]);
    
        $orderParams = Yii::$app->request->get('Order');

        $dateFrom = $orderParams['date_from'] ?? null; // Получение с помощью ??
        $dateTo = $orderParams['date_to'] ?? null;
        $sellerId = $orderParams['seller_id'] ?? null; 
    
        // Применение фильтров к запросу
        if ($dateFrom) {
            $dataProvider->query->where(['>=', 'date_sold', $dateFrom]);
            // $orders->where(['>=', 'date_sold', $dateFrom]); // Применяем фильтры
            $model->date_from = $dateFrom; // Заполнение значения в модели для формы
        }
        if ($dateTo) {
            $dataProvider->query->andWhere(['<=', 'date_sold', $dateTo]);
            // $orders->where(['<=', 'date_sold', $dateTo]);
            $model->date_to = $dateTo; // Заполнение значения в модели для формы
        }
        if ($sellerId) {
            $dataProvider->query->andWhere(['seller_id' => $sellerId]);
            // $orders->where(['seller_id' => $sellerId]);
            $model->seller_id = $sellerId; // Заполнение значения в модели для формы
        }
        $dataProvider->query->orderBy(['date_sold' => SORT_DESC]);

        $sellers = Seller::find()->all();
        $sellerOptions = [];
        foreach ($sellers as $seller) {
            $sellerOptions[$seller->id] = $seller->title;
        }

        return $this->render('index', [
            'orders' => Yii::$app->db->createCommand($dataProvider->query->createCommand()->rawSql)->queryAll(), // Передаем массив orders вместо dataProvider
            'dataProvider' => $dataProvider,
            'sellerOptions' => $sellerOptions,
            'model' => $model, 
        ]);
    }

    public function actionExport()
    {
        $dateFrom = Yii::$app->request->get('date_from');
        $dateTo = Yii::$app->request->get('date_to');
        $sellerId = Yii::$app->request->get('seller_id'); 

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Заголовки столбцов
        $sheet->setCellValue('A1', 'Период времени');
        $sheet->setCellValue('B1', 'Продавец');
        $sheet->setCellValue('C1', 'Среднее количество в заказе');
        $sheet->setCellValue('D1', 'Средняя сумма заказа');
        $sheet->setCellValue('E1', 'Общая сумма продаж');

        // Заполнение данных в отчете
        $row = 2;
        $currentYear = null;
        $currentMonth = null;
        $currentSeller = null;
        $yearTotals = [];
        $monthTotals = [];
        $sellerTotals = [];

        $sql = "SELECT EXTRACT(YEAR FROM date_sold) AS year, EXTRACT(MONTH FROM date_sold) AS month, s.title, AVG(qty) AS avg_qty, AVG(order_sum) AS avg_sum, SUM(order_sum) AS total_sum
            FROM orders o
            JOIN sellers s ON o.seller_id = s.id";

        if ($dateFrom && $dateTo) {
            $sql .= " WHERE date_sold >= TO_DATE('$dateFrom', 'YYYY-MM-DD') AND date_sold <= TO_DATE('$dateTo', 'YYYY-MM-DD')";
        } else if ($dateFrom) {
            $sql .= " WHERE date_sold >= TO_DATE('$dateFrom', 'YYYY-MM-DD')";
        } else if ($dateTo) {
            $sql .= " WHERE date_sold <= TO_DATE('$dateTo', 'YYYY-MM-DD')";
        }

        if ($sellerId) {
            $sql .= ($dateFrom || $dateTo) ? ' AND ' : ' WHERE ';
            $sql .= " seller_id = $sellerId";
        }

        $sql .= " GROUP BY year, month, s.title ORDER BY year, month, s.title";

        $result = Yii::$app->db->createCommand($sql)->queryAll();
        
        foreach ($result as $order) {
            $year = $order['year'];
            $month = $order['month'];
            $seller = $order['title'];

            if ($year !== $currentYear) {
                if ($currentYear !== null) {
                    $sheet->setCellValue('A' . $row, 'Итого за ' . $currentYear);
                    $sheet->setCellValue('E' . $row, '=SUM(E' . ($row - count($monthTotals)) . ':E' . ($row - 1) . ')');
                    $row++;
                }

                $sheet->setCellValue('A' . $row, $year);
                $row++;
                $currentYear = $year;
                $monthTotals = [];
                $yearTotals[$year] = 0;
            }

            if ($month !== $currentMonth) {
                if ($currentMonth !== null) {
                    $sheet->setCellValue('A' . $row, 'Итого за ' . Yii::$app->params['months'][$currentMonth]);
                    $sheet->setCellValue('E' . $row, '=SUM(E' . ($row - count($sellerTotals)) . ':E' . ($row - 1) . ')');
                    $row++;
                }

                $sheet->setCellValue('A' . $row, Yii::$app->params['months'][$month]);
                $row++;
                $currentMonth = $month;
                $sellerTotals = [];
                $monthTotals[$month] = 0;
            }

            if ($seller !== $currentSeller) {
                if ($currentSeller !== null) {
                    $sheet->setCellValue('B' . $row, 'Итого за ' . $currentSeller);
                    $sheet->setCellValue('E' . $row, '=SUM(E' . ($row - count($sellerTotals)) . ':E' . ($row - 1) . ')');
                    $row++;
                }

                $sheet->setCellValue('B' . $row, $seller);
                $sheet->setCellValue('C' . $row, $order['avg_qty']);
                $sheet->setCellValue('D' . $row, $order['avg_sum']);
                $sheet->setCellValue('E' . $row, $order['total_sum']);

                $row++;
                $currentSeller = $seller;
                $sellerTotals[] = $order['total_sum'];
                $monthTotals[$month] += $order['total_sum'];
                $yearTotals[$year] += $order['total_sum'];
            }
        }

        // Итого за последний месяц
        $sheet->setCellValue('A' . $row, 'Итого за ' . date('F', mktime(0, 0, 0, $currentMonth, 10)));
        $sheet->setCellValue('E' . $row, '=SUM(E' . ($row - count($sellerTotals)) . ':E' . ($row - 1) . ')');
        $row++;

        // Итого за последний год
        $sheet->setCellValue('A' . $row, 'Итого за ' . $currentYear);
        $sheet->setCellValue('E' . $row, '=SUM(E' . ($row - count($monthTotals)) . ':E' . ($row - 1) . ')');

        // Создание объекта Writer
        $writer = new Xlsx($spreadsheet);

        // Создание имени файла
        $fileName = 'Таблица_ТЗ_' . date('Y-m-d_His') . '.xlsx';

        // Создание временного файла
        $tempFile = tempnam(sys_get_temp_dir(), 'report');

        // Сохранение файла
        $writer->save($tempFile);

        // Скачивание файла
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        readfile($tempFile);

        // Удаление временного файла
        unlink($tempFile);
    }
    






























    //не смотреть ниже, тут страшно глупо я пытался сделать через DataTable Excel


    // public function actionGetData()
    // {
    //     $model = new Order(); // Создание модели для формы
    //     Yii::$app->response->format = Response::FORMAT_JSON;
    //     $request = Yii::$app->request;

    //     $query = Order::find()->joinWith('seller');

    //     if ($request->post('date_from')) {
    //         $query->where(['>=', 'date_sold', $request->post('date_from')]);
    //     }
    //     if ($request->post('date_to')) {
    //         $query->where(['<=', 'date_sold', $request->post('date_to')]);
    //     }
    //     if ($request->post('seller_id')) {
    //         $query->where(['seller_id' => $request->post('seller_id')]);
    //     }

    //     $dataProvider = new ActiveDataProvider([
    //         'query' => $query,
    //     ]);

    //     $sellers = Seller::find()->all();
    //     $sellerOptions = [];
    //     foreach ($sellers as $seller) {
    //         $sellerOptions[$seller->id] = $seller->title;
    //     }

    //     return [
    //         'draw' => $request->post('draw'),
    //         'recordsTotal' => $dataProvider->getTotalCount(),
    //         'recordsFiltered' => $dataProvider->getTotalCount(),
    //         'data' => $dataProvider->query->all(),
    //         'model' => $model, // Передаем модель
    //         'sellerOptions' => $sellerOptions, // Передаем sellerOptions
    //     ];
    // }
}