<?php

namespace app\api\common\models;

use Yii;

/**
 * This is the model class for table "cv_data_log".
 *
 * @property integer $id
 * @property string $obj_type
 * @property string $action
 * @property string $opt_type
 * @property integer $opt_user_id
 * @property string $opt_user_role
 * @property integer $obj_id
 * @property string $obj_before
 * @property string $obj_after
 * @property string $env
 * @property string $created_at
 */
class DataLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'cv_data_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['opt_user_id', 'obj_id'], 'integer'],
            [['created_at'], 'safe'],
            [['obj_type', 'action', 'opt_type'], 'string', 'max' => 50],
            [['opt_user_role'], 'string', 'max' => 10],
            [['obj_before', 'obj_after'], 'string', 'max' => 1024],
            [['env'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'obj_type' => 'Obj Type',
            'action' => 'Action',
            'opt_type' => 'Opt Type',
            'opt_user_id' => 'Opt User ID',
            'opt_user_role' => 'Opt User Role',
            'obj_id' => 'Obj ID',
            'obj_before' => 'Obj Before',
            'obj_after' => 'Obj After',
            'env' => 'Env',
            'created_at' => 'Created At',
        ];
    }
}
