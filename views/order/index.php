<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/* @var $this yii\web\View */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var app\models\Order $model */
/** @var array $sellers */

$this->title = 'Список заказов';
?>
<div class="order-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать отчет в Excel', ['export',
        'date_from' => $model->date_from,
        'date_to' => $model->date_to,
        'seller_id' => $model->seller_id
        ], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
        // Фильтры
        $form = \yii\widgets\ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => ['class' => 'form-inline']
        ]);

        echo $form->field($model, 'date_from', ['template' => '{label}<div class="input-group mr-2">{input}</div>', 'options' => ['class' => 'form-group']])->textInput(['type' => 'date'])->label('Дата от:');
        echo $form->field($model, 'date_to',   ['template' => '{label}<div class="input-group mr-2">{input}</div>', 'options' => ['class' => 'form-group']])->textInput(['type' => 'date'])->label('Дата до:'); //можно добавить, но не будет календарика ->widget(\yii\widgets\MaskedInput::class, ['mask' => '9999-99-99'])
        echo $form->field($model, 'seller_id', ['template' => '{label}<div class="input-group mr-2">{input}</div>', 'options' => ['class' => 'form-group']])->dropDownList($sellerOptions, ['prompt' => 'Все', 'options' => ['class' => 'form-control']])->label('Продавец:');

        echo Html::submitButton('Применить фильтры', ['class' => 'btn btn-primary']);
        $form->end();
    ?>

    <?php 

        if (!empty($orders)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID заказа</th>
                            <th>Дата заказа</th>
                            <th>Наименование продавца</th>
                            <th>Количество</th>
                            <th>Сумма заказа</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['id'] ?></td>
                                <td><?= $order['date_sold'] ?></td>
                                <td><?= $order['title']?></td>
                                <td><?= $order['qty'] ?></td>
                                <td><?= $order['order_sum'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Заказы не найдены.</p>
            <?php endif;
    


    // ниже не смотреть




    // GridView::widget([
    //     'dataProvider' => $dataProvider,
    //     'columns' => [
    //         'id',
    //         'date_sold',
    //         'seller.title',
    //         'qty',
    //         'order_sum',
    //     ],
    // ]); 
    ?>
    <!-- <script src="<?= Yii::getAlias('@web') ?>/assets/7fe67dab/jquery.min.js"></script> -->
    <!-- <link rel="stylesheet" href="<?= Yii::getAlias('@web') ?>/vendor/yajra/laravel-datatables-oracle/src/resources/views/vendor/datatables/jquery.dataTables.min.css">
    <link rel="stylesheet" href="<?= Yii::getAlias('@web') ?>/vendor/yajra/laravel-datatables-oracle/src/resources/views/vendor/datatables/buttons.dataTables.min.css">
    <script src="<?= Yii::getAlias('@web') ?>/vendor/yajra/laravel-datatables-oracle/src/resources/views/vendor/datatables/buttons.dataTables.min.js"></script>
    <script src="<?= Yii::getAlias('@web') ?>/vendor/yajra/laravel-datatables-oracle/src/resources/views/vendor/datatables/jquery.dataTables.min.js"></script>
     -->
    <!-- <script>
        $(document).ready(function() {
            $('#orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '<?= \yii\helpers\Url::toRoute('order/get-data') ?>',
                    type: 'POST',
                    data: function(d) {
                        d.date_from = $('#order-date_from').val();
                        d.date_to = $('#order-date_to').val();
                        d.seller_id = $('#order-seller_id').val();
                    },
                    success: function (response) {
                        if(response) {
                            console.log(response);
                            // $(document).html(response.html);
                        }
                    },
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel-o" aria-hidden="true"></i> Excel',
                        sToolTip: 'Сохранить как Excel',
                        className: 'btn-sm btn-outline-primary',
                        title: 'Таблица'
                    }
                ],
                columns: [
                    {data: 'id'},
                    {data: 'date_sold'},
                    {data: 'seller.title'},
                    {data: 'qty'},
                    {data: 'order_sum'}
                ]
            });
        });
    </script>

    <div class="col-md-12">
        <table id="orders-table" class="table table-striped">
            <thead>
                <tr>
                    <th>ID заказа</th>
                    <th>Дата заказа</th>
                    <th>Наименование продавца</th>
                    <th>Количество</th>
                    <th>Сумма заказа</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div> -->

</div>