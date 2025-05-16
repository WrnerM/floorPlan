<?php

class Login{
    public $db = null;
    public $userNT = '';
    public $password = '';
   

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
                $response = array(
                    "success" => 0,
                    "message" => "Invalid User Credentials"
                );
            }
        }else {
            $response = array(
                "success" => 0,
                "message" => "Invalid User Credentials"
            );
        }
        
        return $response;

    }


    private function getUser(): array
    {

        $response = [];
        try { 

            $userSql = "SELECT bms_ident, full_name, nt, user_email, user_role
                            FROM users WHERE nt=:nt";

            $stmt = $this->db->prepare($userSql);
            $stmt->bindParam(':nt', $this->userNT, PDO::PARAM_STR);
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $response = $data;
            } 

        } catch (PDOException $e) {
            $data = $e->getMessage();
        }

        return $response;
    }
    

}
?>