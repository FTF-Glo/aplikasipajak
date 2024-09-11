<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Html {
	public function input_group($label, $name, $value='', $type="text", $placeholder='',$class='',$other="", $warn=""){
		$input = '<div class="form-group">';
		$input .= '<label for="'.$name.'">'.$label.'</label>';
		$input .= '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" '.$other.'/>';
		$input .= $warn.'</div>';
		return $input;
	}
	public function input_group_addon($label, $name, $addon, $value='', $type="text", $placeholder='',$class='', $other=''){
		$input = '<div class="form-group">';
		$input .= '<label for="'.$name.'">'.$label.'</label>';
		$input .= '<div class="input-group">';
		$input .= '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" '.$other.'/>';
		$input .= '<div class="input-group-append"><span class="input-group-text">'.$addon.'</span></div>';
		$input .= '</div></div>';
		return $input;
	}
	public function input_group_req($label, $name, $value='', $type="text", $placeholder='',$class='', $other=''){
		$input = '<div class="form-group">';
		$input .= '<label for="'.$name.'">'.$label.'</label>';
		$input .= '<input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" required />';
		$input .= '</div>';
		return $input;
	}
	public function input_group_hor($label, $name, $value='', $type="text", $placeholder='',$class='', $other=''){
		$input = '<div class="form-group row">';
		$input .= '<label class="col-md-3 label-control" for="'.$name.'">'.$label.'</label>';
		$input .= '<div class="col-md-9"><input type="'.$type.'" name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" required /></div>';
		$input .= '</div>';
		return $input;
	}
	
	public function textarea_group($label, $name, $value='', $placeholder='',$class='', $other=''){
		$input = '<div class="form-group">';
		$input .= '<label for="'.$name.'">'.$label.'</label>';
		$input .= '<textarea name="'.$name.'" id="'.$name.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" '.$other.'>'.$value.'</textarea>';
		$input .= '</div>';
		return $input;
	}
	public function textarea_group_hor($label, $name, $value='', $placeholder='',$class='', $other=''){
		$input = '<div class="form-group row">';
		$input .= '<label class="col-md-3 label-control" for="'.$name.'">'.$label.'</label>';
		$input .= '<div class="col-md-9"><textarea name="'.$name.'" id="'.$name.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" '.$other.'>'.$value.'</textarea></div>';
		$input .= '</div>';
		return $input;
	}
	public function input($name, $value='', $placeholder='',$class='',$other=""){
		$input = '<input name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" '.$other.' />';
		return $input;
	}
	public function input_req($name, $value='', $placeholder='',$class=''){
		$input = '<input name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" required >';
		return $input;
	}
	public function input_lock($name, $value='', $placeholder='',$class=''){
		$input = '<input name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" readonly >';
		return $input;
	}
	public function input_hide($name, $value='', $placeholder='',$class=''){
		$input = '<input name="'.$name.'" id="'.$name.'" value="'.$value.'" class="form-control '.$class.'" placeholder="'.$placeholder.'" hidden>';
		return $input;
	}
	
	
	

	public function href($link, $text, $class='', $attr=''){
		return '<a href="'.$link.'" class="'.$class.'" '.$attr.'>'.$text.'</a>';
	}
	
	public function href_btnicon($link, $icon, $info){
		return '<a href="'.base_url().$link.'" class="btn btn-icon btn-sm"  data-toggle="tooltip" data-placement="top" title="'.$info.'"><i class="'.$icon.'"></i></a>';
	}		
	
	public function last_uri_string(){
		$uri = uri_string();
		$uri = explode("/",$uri);
		$uri = current($uri);
		return $uri;
	}
	
	public function alert_success_del($name){
		$result = '<div class="alert alert-success mb-2" role="alert">Data <strong>'.$name.'</strong> berhasil dihapus!</div>';
		return $result;
	}

	public function alert_success_add($name){
		$result = '<div class="alert alert-success mb-2" role="alert">Data <strong>'.$name.'</strong> berhasil ditambahkan!</div>';
		return $result;
	}
	public function alert_success_edit($name){
		$result = '<div class="alert alert-success mb-2" role="alert">Data <strong>'.$name.'</strong> berhasil perbaharui!</div>';
		return $result;
	}
	public function btn_icon($title, $icon, $class, $pass="",$other="",$tip=""){
		$res = '<button '.$other.' class="btn btn-icon '.$class.'" data-toggle="tooltip'.$tip.'" data-placement="top" data-original-title="'.$title.'">
					<i class="'.$icon.'"></i>'.$pass.'
			    </button>
			   ';
		return $res;
		
	}
	public function button($title, $icon, $class, $pass="",$other="",$tip=""){
		$res = '<button '.$other.' class="btn '.$class.'" data-toggle="tooltip'.$tip.'" data-placement="top" data-original-title="'.$title.'">
					<i class="'.$icon.'"></i>'.$pass.'
			    </button>
			   ';
		return $res;
		
	}
}

