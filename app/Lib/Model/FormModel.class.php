<?php
class FormModel extends Model {
  //自动验证
  protected $_validate = array(
    array('words', 'require', '单词必须', 1),
    array('words', '', '单词已存在', 0, 'unique', self::MODEL_INSERT),
    array('mean', 'require', '解释必须'),
  );
  //自动填充
  protected $_auto = array(
    array('create_time', 'time', self::MODEL_INSERT, 'function'),
  );

}

