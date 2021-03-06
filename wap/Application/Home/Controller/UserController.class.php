<?php
namespace Home\Controller;

class UserController extends HomeController
{
	public function index()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);
		$this->assign('prompt_text', D('Text')->get_content('user_index'));
		$this->display();
	}

	public function nameauth()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if ($user['idcard']) {
			$user['idcard'] = substr_replace($user['idcard'], '********', 6, 8);
		}

		$this->assign('user', $user);
		$this->assign('prompt_text', D('Text')->get_content('user_nameauth'));
		$this->display();
	}

	public function password()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('user_password'));
		$this->display();
	}

	public function uppassword($oldpassword, $newpassword, $repassword)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!check($oldpassword, 'password')) {
			$this->error('旧登录密码格式错误！');
		}

		if (!check($newpassword, 'password')) {
			$this->error('新登录密码格式错误！');
		}

		if ($newpassword != $repassword) {
			$this->error('确认新密码错误！');
		}

		$password = M('User')->where(array('id' => userid()))->getField('password');

		if (md5($oldpassword) != $password) {
			$this->error('旧登录密码错误！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('password' => md5($newpassword)));

		if ($rs) {
			$this->success('修改成功');
		}
		else {
			$this->error('修改失败');
		}
	}

	public function paypassword()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$this->assign('prompt_text', D('Text')->get_content('user_paypassword'));
		$this->display();
	}

	public function uppaypassword($oldpaypassword, $newpaypassword, $repaypassword)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!check($oldpaypassword, 'password')) {
			$this->error('旧交易密码格式错误！');
		}

		if (!check($newpaypassword, 'password')) {
			$this->error('新交易密码格式错误！');
		}

		if ($newpaypassword != $repaypassword) {
			$this->error('确认新密码错误！');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (md5($oldpaypassword) != $user['paypassword']) {
			$this->error('旧交易密码错误！');
		}

		if (md5($newpaypassword) == $user['password']) {
			$this->error('交易密码不能和登录密码相同！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('paypassword' => md5($newpaypassword)));

		if ($rs) {
			$this->success('修改成功');
		}
		else {
			$this->error('修改失败');
		}
	}

	public function ga()
	{
		if (empty($_POST)) {
			if (!userid()) {
				redirect('/#login');
			}

			$this->assign('prompt_text', D('Text')->get_content('user_ga'));
			$user = M('User')->where(array('id' => userid()))->find();
			$is_ga = ($user['ga'] ? 1 : 0);
			$this->assign('is_ga', $is_ga);

			if (!$is_ga) {
				$ga = new \Common\Ext\GoogleAuthenticator();
				$secret = $ga->createSecret();
				session('secret', $secret);
				$this->assign('Asecret', $secret);
				$qrCodeUrl = $ga->getQRCodeGoogleUrl($user['username'] . '%20-%20' . $_SERVER['HTTP_HOST'], $secret);
				$this->assign('qrCodeUrl', $qrCodeUrl);
				$this->display();
			}
			else {
				$arr = explode('|', $user['ga']);
				$this->assign('ga_login', $arr[1]);
				$this->assign('ga_transfer', $arr[2]);
				$this->display();
			}
		}
		else {
			if (!userid()) {
				$this->error('登录已经失效,请重新登录!');
			}

			$delete = '';
			$gacode = trim(I('ga'));
			$type = trim(I('type'));
			$ga_login = (I('ga_login') == false ? 0 : 1);
			$ga_transfer = (I('ga_transfer') == false ? 0 : 1);

			if (!$gacode) {
				$this->error('请输入验证码!');
			}

			if ($type == 'add') {
				$secret = session('secret');

				if (!$secret) {
					$this->error('验证码已经失效,请刷新网页!');
				}
			}
			else if (($type == 'update') || ($type == 'delete')) {
				$user = M('User')->where('id = ' . userid())->find();

				if (!$user['ga']) {
					$this->error('还未设置谷歌验证码!');
				}

				$arr = explode('|', $user['ga']);
				$secret = $arr[0];
				$delete = ($type == 'delete' ? 1 : 0);
			}
			else {
				$this->error('操作未定义');
			}

			$ga = new \Common\Ext\GoogleAuthenticator();

			if ($ga->verifyCode($secret, $gacode, 1)) {
				$ga_val = ($delete == '' ? $secret . '|' . $ga_login . '|' . $ga_transfer : '');
				M('User')->save(array('id' => userid(), 'ga' => $ga_val));
				$this->success('操作成功');
			}
			else {
				$this->error('验证失败');
			}
		}
	}

	public function moble()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if ($user['moble']) {
			$user['moble'] = substr_replace($user['moble'], '****', 3, 4);
		}

		$this->assign('user', $user);
		$this->assign('prompt_text', D('Text')->get_content('user_moble'));
		$this->display();
	}

	public function upmoble($moble, $moble_verify)
	{
		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($moble, 'moble')) {
			$this->error('手机号码格式错误！');
		}

		if (!check($moble_verify, 'd')) {
			$this->error('短信验证码格式错误！');
		}

		if ($moble_verify != session('real_verify')) {
			$this->error('短信验证码错误！');
		}

		if (M('User')->where(array('moble' => $moble))->find()) {
			$this->error('手机号码已存在！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('moble' => $moble, 'mobletime' => time()));

		if ($rs) {
			$this->success('手机认证成功！');
		}
		else {
			$this->error('手机认证失败！');
		}
	}

	public function alipay()
	{
		if (!userid()) {
			redirect('/#login');
		}

		D('User')->check_update();
		$this->assign('prompt_text', D('Text')->get_content('user_alipay'));
		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('user', $user);
		$this->display();
	}

	public function upalipay($alipay = NULL, $paypassword = NULL)
	{
		if (!userid()) {
			$this->error('您没有登录请先登录！');
		}

		if (!check($alipay, 'moble')) {
			if (!check($alipay, 'email')) {
				$this->error('支付宝账号格式错误！');
			}
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$user = M('User')->where(array('id' => userid()))->find();

		if (md5($paypassword) != $user['paypassword']) {
			$this->error('交易密码错误！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('alipay' => $alipay));

		if ($rs) {
			$this->success('支付宝认证成功！');
		}
		else {
			$this->error('支付宝认证失败！');
		}
	}

	public function tpwdset()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$user = M('User')->where(array('id' => userid()))->find();
		$this->assign('prompt_text', D('Text')->get_content('user_tpwdset'));
		$this->assign('user', $user);
		$this->display();
	}

	public function tpwdsetting()
	{
		if (userid()) {
			$tpwdsetting = M('User')->where(array('id' => userid()))->getField('tpwdsetting');
			exit($tpwdsetting);
		}
	}

	public function uptpwdsetting($paypassword, $tpwdsetting)
	{
		if (!userid()) {
			$this->error('请先登录！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (($tpwdsetting != 1) && ($tpwdsetting != 2) && ($tpwdsetting != 3)) {
			$this->error('选项错误！' . $tpwdsetting);
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		$rs = M('User')->where(array('id' => userid()))->save(array('tpwdsetting' => $tpwdsetting));

		if ($rs) {
			cookie('tpwdsetting_'.userid(),$paypassword,2592000);
			$this->success('成功！');
		}
		else {
			$this->error('失败！');
		}
	}

	public function bank()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$UserBankType = M('UserBankType')->where(array('status' => 1))->order('id desc')->select();
		$this->assign('UserBankType', $UserBankType);
		$truename = M('User')->where(array('id' => userid()))->getField('truename');
		$this->assign('truename', $truename);
		$UserBank = M('UserBank')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();
		
		$UserBankcount = M('UserBank')->field('id')->where(array('userid' => userid(), 'status' => 1))->count();
		
		
		$this->assign('UserBankcount', $UserBankcount);
		$this->assign('UserBank', $UserBank);
		$this->assign('prompt_text', D('Text')->get_content('user_bank'));
		$this->display();
	}

	public function upbank($name, $bank, $bankprov, $bankcity, $bankaddr, $bankcard, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}

		if (!check($bank, 'a')) {
			$this->error('开户银行格式错误！');
		}

		if (!check($bankprov, 'c')) {
			$this->error('开户省市格式错误！');
		}

		if (!check($bankcity, 'c')) {
			$this->error('开户省市格式错误2！');
		}

		if (!check($bankaddr, 'a')) {
			$this->error('开户行地址格式错误！');
		}

		if (!check($bankcard, 'd')) {
			$this->error('银行账号格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('UserBankType')->where(array('title' => $bank))->find()) {
			$this->error('开户银行错误！');
		}

		$userBank = M('UserBank')->where(array('userid' => userid()))->select();

		foreach ($userBank as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的备注名称！');
			}

			if ($v['bankcard'] == $bankcard) {
				$this->error('银行卡号已存在！');
			}
		}

		if (10 <= count($userBank)) {
			$this->error('每个用户最多只能添加10个地址！');
		}

		if (M('UserBank')->add(array('userid' => userid(), 'name' => $name, 'bank' => $bank, 'bankprov' => $bankprov, 'bankcity' => $bankcity, 'bankaddr' => $bankaddr, 'bankcard' => $bankcard, 'addtime' => time(), 'status' => 1))) {
			$this->success('银行添加成功！');
		}
		else {
			$this->error('银行添加失败！');
		}
	}

	public function delbank($id, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('UserBank')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error('非法访问！');
		}
		else if (M('UserBank')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success('删除成功！');
		}
		else {
			$this->error('删除失败！');
		}
	}

	public function qianbao($coin = NULL)
	{
		if (!userid()) {
			redirect('/#login');
		}

		$Coin = M('Coin')->where(array(
			'status' => 1,
			'name'   => array('neq', 'cny')
			))->select();

		if (!$coin) {
			$coin = $Coin[0]['name'];
		}

		$this->assign('xnb', $coin);
		$this->assign('xnb_title', C('coin')[$coin]['title']);
		foreach ($Coin as $k => $v) {
			$coin_list[$v['name']] = $v;
		}

		$this->assign('coin_list', $coin_list);
		$userQianbaoList = M('UserQianbao')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->order('id desc')->select();
		$UserBankcount = M('UserQianbao')->field('id')->where(array('userid' => userid(), 'status' => 1, 'coinname' => $coin))->count();
		$this->assign('UserBankcount', $UserBankcount);
		$this->assign('userQianbaoList', $userQianbaoList);
		$this->assign('prompt_text', D('Text')->get_content('user_qianbao'));
		$this->display();
	}

	public function upqianbao($coin, $name, $addr, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}

		if (!check($addr, 'dw')) {
			$this->error('钱包地址格式错误！');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('Coin')->where(array('name' => $coin))->find()) {
			$this->error('币种错误！');
		}

		$userQianbao = M('UserQianbao')->where(array('userid' => userid(), 'coinname' => $coin))->select();

		foreach ($userQianbao as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的钱包标识！');
			}

			if ($v['addr'] == $addr) {
				$this->error('钱包地址已存在！');
			}
		}

		if (10 <= count($userQianbao)) {
			$this->error('每个人最多只能添加10个地址！');
		}

		if (M('UserQianbao')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'coinname' => $coin, 'addtime' => time(), 'status' => 1))) {
			$this->success('添加成功！');
		}
		else {
			$this->error('添加失败！');
		}
	}

	public function delqianbao($id, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error('非法访问！');
		}
		else if (M('UserQianbao')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success('删除成功！');
		}
		else {
			$this->error('删除失败！');
		}
	}

	public function goods()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$userGoodsList = M('UserGoods')->where(array('userid' => userid(), 'status' => 1))->order('id desc')->select();

		foreach ($userGoodsList as $k => $v) {
			$userGoodsList[$k]['moble'] = substr_replace($v['moble'], '****', 3, 4);
			$userGoodsList[$k]['idcard'] = substr_replace($v['idcard'], '********', 6, 8);
		}

		$this->assign('userGoodsList', $userGoodsList);
		$this->assign('prompt_text', D('Text')->get_content('user_goods'));
		$this->display();
	}

	public function upgoods($name, $truename, $idcard, $moble, $addr, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($name, 'a')) {
			$this->error('备注名称格式错误！');
		}

		if (!check($truename, 'truename')) {
			$this->error('联系姓名格式错误！');
		}

		if (!check($idcard, 'idcard')) {
			$this->error('身份证号格式错误！');
		}

		if (!check($moble, 'moble')) {
			$this->error('联系电话格式错误！');
		}

		if (!check($addr, 'a')) {
			$this->error('联系地址格式错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		$userGoods = M('UserGoods')->where(array('userid' => userid()))->select();

		foreach ($userGoods as $k => $v) {
			if ($v['name'] == $name) {
				$this->error('请不要使用相同的地址标识！');
			}
		}

		if (10 <= count($userGoods)) {
			$this->error('每个人最多只能添加10个地址！');
		}

		if (M('UserGoods')->add(array('userid' => userid(), 'name' => $name, 'addr' => $addr, 'idcard' => $idcard, 'truename' => $truename, 'moble' => $moble, 'addtime' => time(), 'status' => 1))) {
			$this->success('添加成功！');
		}
		else {
			$this->error('添加失败！');
		}
	}

	public function delgoods($id, $paypassword)
	{
		if (!userid()) {
			redirect('/#login');
		}

		if (!check($paypassword, 'password')) {
			$this->error('交易密码格式错误！');
		}

		if (!check($id, 'd')) {
			$this->error('参数错误！');
		}

		$user_paypassword = M('User')->where(array('id' => userid()))->getField('paypassword');

		if (md5($paypassword) != $user_paypassword) {
			$this->error('交易密码错误！');
		}

		if (!M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->find()) {
			$this->error('非法访问！');
		}
		else if (M('UserGoods')->where(array('userid' => userid(), 'id' => $id))->delete()) {
			$this->success('删除成功！');
		}
		else {
			$this->error('删除失败！');
		}
	}

	public function log()
	{
		if (!userid()) {
			redirect('/#login');
		}

		$where['status'] = array('egt', 0);
		$where['userid'] = userid();
		$Model = M('UserLog');
		$count = $Model->where($where)->count();
		$Page = new \Think\Page($count, 10);
		$show = $Page->show();
		$list = $Model->where($where)->order('id desc')->limit($Page->firstRow . ',' . $Page->listRows)->select();
		$this->assign('list', $list);
		$this->assign('page', $show);
		$this->assign('prompt_text', D('Text')->get_content('user_log'));
		$this->display();
	}

	public function install()
	{
	}

	public function check()
	{

	}
}

?>