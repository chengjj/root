<?php
class FormModel extends Model {
  //�Զ���֤
  protected $_validate = array(
    array('words', 'require', '���ʱ���', 1),
    array('words', '', '�����Ѵ���', 0, 'unique', self::MODEL_INSERT),
    array('mean', 'require', '���ͱ���'),
  );
  //�Զ����
  protected $_auto = array(
    array('create_time', 'time', self::MODEL_INSERT, 'function'),
  );

}
