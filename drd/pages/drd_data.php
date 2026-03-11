<?php
class Dbcon
{
	// private $dbName = 'demo_tngc_hrd2';
	// private $dbName = 'tngc_hrd2';
	// private $dbName = 'tngc_hrdserver3' ;
	private $dbName = 'portal_db';
	private $dbHost = 'localhost';
	private $dbUsername = 'root';
	private $dbUserPassword = '';

	// private $dbHost = 'localhost';
	// private $dbUsername = 'misadmin';
	// private $dbUserPassword = '88224646abxy@';
	protected $cont  = null;

	function connect()
	{
		if (empty($this->cont)) {
			$dsn = 'mysql:host=' . $this->dbHost . ';dbname=' . $this->dbName;
			$this->cont = new PDO($dsn, $this->dbUsername, $this->dbUserPassword, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
			$this->cont->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		}
		return $this->cont;
	}

	function disconnect()
	{
		$this->cont  = null;
		return $this->cont;
	}
}

$db = new Dbcon;
$con1 = $db->connect();
// $trans = new Transactions;
// $con1 = $trans->connect();

$drd_id = !empty($_POST["get_drd"]) ? $_POST["get_drd"] : exit;
foreach ($con1->query("SELECT * FROM tbl201_drd_details WHERE drdd_drdid=" . $drd_id) as $drd) {
?>
	<tr>
		<td style="min-width: 200px; max-width: 230px;">
			<input type="hidden" name="drd_id" value="<?= $drd["drdd_id"] ?>">
			<input type="date" name="drd_date" class="form-control" value="<?= date("Y-m-d", strtotime($drd["drdd_date"])) ?>" required>
		</td>
		<td style="min-width: 300px;">
			<textarea name="drd_purpose" class="form-control" required><?= $drd["drdd_purpose"] ?></textarea>
		</td>
		<td>
			<button type='button' class='btn btn-danger btn-sm' onclick='remove_drd_row(this)'><i class='fa fa-times'></i></button>
		</td>
	</tr>
<?php
}

$con1 = $db->disconnect();
