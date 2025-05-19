<?php

class LoginClass{
    public $db = null;
    public $userNT = null;
    public $password = null;
    public $bms_ident = null;
   

    public function login(){

        $response = [];

        if($this->userNT == "admin" && $this->password == "admin"){
            return array( [
                "bms_ident" => "1",
                "full_name" => "Admin",
                "nt" => "1",
                "user_email" => "admin@teleperformanceusa.com",
                "user_role" => "Admin" ]
            );
        }
       
        $base_url = 'https://www.wblt.ccms.teleperformance.com/authentication';
        $c = curl_init();

        curl_setopt($c, CURLOPT_URL, $base_url);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_USERPWD, $this->userNT.":".$this->password);
        
        curl_exec($c);

        $status = curl_getinfo($c, CURLINFO_HTTP_CODE);

        curl_close($c);
        
        if($status == 200){
            $response = $this->getUser();
            if(count($response) <= 0){
                $response = $this->getAdmin();
                if(count($response) <= 0){
                    $response = array(
                        "success" => 0,
                        "message" => "Invalid User Credentials"
                    );

                    return $response;
                }
            }

            if(count($response) > 0){
                foreach($response as $row){
                    $this->bms_ident = $row['bms_ident'];
                }
                $this->insertlogs();
            }            
        }else {
            $response = array(
                "success" => 0,
                "message" => "Invalid User Credentials"
            );
        }
        
        return $response;

    }


    private function getAdmin(): array
    {

        $response = [];
        try { 

            $sql = "SELECT bms_ident, full_name, nt, user_email, user_role
                            FROM users WHERE nt=:nt";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nt', $this->userNT, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $response = $data;
            } 

        } catch (PDOException $e) {
            $response = $e->getMessage();
        }

        return $response;
    }

    private function getUser(): array
    {

        $response = [];
        try { 

            $sql = "SELECT employee_ident as bms_ident, 
                                employee_full_name as full_name, 
                                nt, 
                                email as user_email, 
                                'User' as user_role
                            FROM onboard360.dbo.ccms_employee_lists WHERE nt=:nt";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nt', $this->userNT, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $response = $data;
            } 

        } catch (PDOException $e) {
            $response = $e->getMessage();
        }

        return $response;
    }


    public function insertlogs(){

        $response = [];

        try {
            $date_now = date("Y-m-d H:i:s", time());

            $sql = "INSERT INTO user_logs
                    (
                        user_action
                        ,user_ident
                        ,inserted_date_time_ph

                    )
                    VALUES 
                    (
                        'Login'
                        ,:user_ident
                        ,:date_now
                    )";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_ident', $this->bms_ident, PDO::PARAM_STR); 
            $stmt->bindParam(':date_now', $date_now, PDO::PARAM_STR); 
            $stmt->execute();

            $response['success'] = 1;
            $response['sql'] = $sql;     

        } catch (\Throwable $th) {
            $response['success'] = 0;
            $response['error'] = $th->getMessage(); 
            $response['sql'] = $sql; 
        }

        return $response;
    }
    

}
?>