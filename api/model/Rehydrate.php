<?php

class Rehydrate{
    public $db = null;
    public $bms_ident = null;


    public function getAdmin()
    {

        $response = [];
        try { 

            $sql = "SELECT bms_ident, full_name, nt, user_email, user_role
                            FROM users WHERE bms_ident = :bms_ident";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':bms_ident', $this->bms_ident, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $response = $data[0];
                $response['success'] = 1;
            } 

        } catch (PDOException $e) {
            $response = array(
                "success" => 0,
                "message" => $e->getMessage()
            );
        }

        return $response;
    }
    

}
?>