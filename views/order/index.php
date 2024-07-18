<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/* @var $this yii\web\View */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $sellers */

$this->title = 'Список заказов';
?>
<div class="order-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Создать отчет в Excel', ['export'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php
        // Фильтры
        $form = \yii\widgets\ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options' => ['class' => 'form-inline']
        ]);

        echo $form->field($model, 'date_from', ['template' => '{label}<div class="input-group mr-2">{input}{append}</div>', 'options' => ['class' => 'form-group']])->textInput(['type' => 'date'])->label('Дата от:')->widget(\yii\widgets\MaskedInput::class, ['mask' => '9999-99-99']);
        echo $form->field($model, 'date_to', ['template' => '{label}<div class="input-group mr-2">{input}{append}</div>', 'options' => ['class' => 'form-group']])->textInput(['type' => 'date'])->label('Дата до:')->widget(\yii\widgets\MaskedInput::class, ['mask' => '9999-99-99']);
        echo $form->field($model, 'seller_id', ['template' => '{label}<div class="input-group mr-2">{input}{append}</div>', 'options' => ['class' => 'form-group']])->dropDownList($sellers, ['prompt' => 'Все', 'options' => ['class' => 'form-control']])->label('Продавец:');

        echo Html::submitButton('Применить фильтры', ['class' => 'btn btn-primary']);
        $form->end();
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['attribute' => 'id', 'label' => 'ID заказа'],
            ['attribute' => 'date_sold', 'label' => 'Дата заказа'],
            [
                'attribute' => 'seller.title',
                'label' => 'Наименование продавца',
            ],
            ['attribute' => 'qty', 'label' => 'Количество'],
            ['attribute' => 'order_sum', 'label' => 'Сумма заказа'],
        ],
    ]); ?>

</div>