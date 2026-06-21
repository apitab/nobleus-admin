<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var backend\models\Permissions $model */
/** @var yii\widgets\ActiveForm $form */
?>
<?php $form = ActiveForm::begin(); ?>
<div class="card-body">
    <?= $form->field($model, 'group_id')->dropDownList(ArrayHelper::map($groups, 'id','name'),['prompt' => '-- Select Group --','class' => 'form-select']) ?>  
    <?= $form->field($model, 'module_id')->dropDownList(ArrayHelper::map($modules, 'id','description'),['prompt' => '-- Select Module --','class' => 'form-select']) ?> 
    <?= $form->field($model, 'action_id')->dropDownList(ArrayHelper::map($module_actions, 'id','description'),['prompt' => '-- Select Module Action --','class' => 'form-select']) ?> 
</div>
<div class="card-footer">
    <?= Html::submitButton($model->isNewRecord ? 'Save' : 'Save Changes', ['class' => 'btn btn-brand-02']) ?> or <a href="<?= Yii::$app->request->referrer ?>">Cancel</a>
</div>
<?php ActiveForm::end(); ?>