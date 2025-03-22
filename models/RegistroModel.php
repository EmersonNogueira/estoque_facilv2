<?php
    namespace models;

    class RegistroModel extends Model
    {

        public function compra($codigo_item,$saldo_alocar) {

            var_dump($codigo_item);
            var_dump($saldo_alocar);

            


        }

        public function setValorUnitario($codigo_item, $custo_unitario) {
            $sql = "UPDATE itens SET custo_unitario = :custo_unitario WHERE codigo_item = :codigo_item";
        
            $stmt = $this->pdo->prepare($sql);
        
            $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
            $stmt->bindParam(':custo_unitario', $custo_unitario, \PDO::PARAM_STR); // Se for decimal, pode ser tratado como string
        
            return $stmt->execute();
        }
        
    }
?>