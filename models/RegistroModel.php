<?php
    namespace models;

    class RegistroModel extends Model
    {


        public function setValorUnitario($codigo_item, $custo_unitario) {
            $sql = "UPDATE itens SET custo_unitario = :custo_unitario WHERE codigo_item = :codigo_item";
        
            $stmt = $this->pdo->prepare($sql);
        
            $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
            $stmt->bindParam(':custo_unitario', $custo_unitario, \PDO::PARAM_STR); // Se for decimal, pode ser tratado como string
        
            return $stmt->execute();
        }

        public function compra($quantidade, $codigo_item, $numero_nota, $data, $custo) {
            $tipo = "Compra";
            $id_usuario = $_SESSION['id'] ?? null; // Garante que id_usuario pode ser NULL se não estiver definido
        
            try {
                // Inicia a transação
                $this->pdo->beginTransaction();
        
                // Insere o registro na tabela registros
                $sql = "INSERT INTO registros (tipo, quantidade, codigo_item, numero_nota, data_registro, custo, id_usuario) 
                        VALUES (:tipo, :quantidade, :codigo_item, :numero_nota, :data_registro, :custo, :id_usuario)";
        
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':tipo', $tipo, \PDO::PARAM_STR);
                $stmt->bindParam(':quantidade', $quantidade, \PDO::PARAM_INT);
                $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
                $stmt->bindParam(':numero_nota', $numero_nota, \PDO::PARAM_STR);
                $stmt->bindParam(':data_registro', $data, \PDO::PARAM_STR);
                $stmt->bindParam(':custo', $custo, \PDO::PARAM_STR);
                $stmt->bindParam(':id_usuario', $id_usuario, $id_usuario ? \PDO::PARAM_INT : \PDO::PARAM_NULL);
        
                $stmt->execute();
        
                // Atualiza o custo unitário do item na tabela itens
                $this->setValorUnitario($codigo_item, $custo);
        
                // Confirma a transação
                $this->pdo->commit();
        
                return true;
            } catch (Exception $e) {
                // Em caso de erro, desfaz a transação
                $this->pdo->rollBack();
        
                // Registra o erro (pode ser alterado para log de sistema)
                error_log("Erro na compra: " . $e->getMessage());
        
                return false;
            }
        }
        

        public function setSaldo_alocar($codigo_item, $saldo_alocar) {
            $sql = "UPDATE itens SET saldo_alocar = :saldo_alocar WHERE codigo_item = :codigo_item";
        
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':codigo_item', $codigo_item, \PDO::PARAM_INT);
            $stmt->bindParam(':saldo_alocar', $saldo_alocar, \PDO::PARAM_INT); 
        
            return $stmt->execute();
        }
        
        
        
    }
?>