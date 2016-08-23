<?php
/**
 * VPP Exceptioanl PNs
 */

require_once('common.php');
Server::$force_production = true;

$app->get('/apple_pns', function () use ($db) {

        $query = "SELECT exportPN FROM tekpartnumbers WHERE manufacturer IN ('Apple','apple')";
        $result = $db->query($query);

        $rows = array();
        while($row = $result->fetch_object()){
                $rows[] = $row;
        }

        if($result){
                echo Format::prettyPrint(json_encode($rows));
        }else{
                echo json_encode($db->error);
        }
});

$app->get('/vpp_exception_pns', function () use ($db) {

	$query = "SELECT * FROM vpp_pn_exceptions";
	$result = $db->query($query);

	$rows = array();
	while($row = $result->fetch_object()){
		//$rows[$row->department][] = $row;
		$rows[] = $row;
	}

	if($result){
		echo Format::prettyPrint(json_encode($rows));
	}else{
		echo json_encode($db->error);
	}
});

$app->get('/vpp_exception_latest_pn', function () use ($db) {

        $query = "SELECT * FROM vpp_pn_exceptions ORDER BY id DESC LIMIT 1"; 
        $result = $db->query($query);
        $row = $result->fetch_object();

        if($result){
                echo json_encode($row);
        }else{
                echo json_encode($db->error);
        }
});

$app->get('/vpp_exception_pns/:vpp_contract_id', function ($vpp_contract_id) use ($db) {

        $query = "SELECT * FROM vpp_pn_exceptions WHERE vpp_contract_id = $vpp_contract_id";
        $result = $db->query($query);
	if(!$result) die($db->error);

        $rows = array();
        while($row = $result->fetch_object()){
                //$rows[$row->department][] = $row;
                $rows[] = $row;
        }

        if($result){
                echo Format::prettyPrint(json_encode($rows));
        }else{
                echo json_encode($db->error);
        }
});

$app->post('/vpp_exception_pns', function () use ($app, $db) {

        $data  = json_decode($app->request->getBody(), true);

        $query = "INSERT INTO vpp_pn_exceptions (vpp_contract_id, ExportPN, discount) VALUES
                               ('".$data['vpp_contract_id']."',
                                '".$data['ExportPN']."',
                                '".$data['discount']."')";
        $result = $db->query($query);
        if($result){
        	$query = "SELECT id FROM vpp_pn_exceptions ORDER BY id DESC LIMIT 1";
        	$result = $db->query($query);
        	$row = $result->fetch_object();
		if($result) $data['id']=$row->id;

                echo json_encode($data);
        }else{
                echo json_encode($db->error);
        }
});

$app->delete('/vpp_exception_pns/:id', function ($id) use ($db) {

    $query = "DELETE FROM vpp_pn_exceptions WHERE id = '".$id."'";
    $result = $db->query($query);
    if($result){
        echo json_encode(true);
    }else{
        echo json_encode($db->error);
    }
});

?>
