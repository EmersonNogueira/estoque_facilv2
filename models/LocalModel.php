<?php
    namespace models;

    class LocalModel extends Model
    {

        public function locais_depositos() {
            try {
                // Consulta SQL com JOIN entre locais e depositos
                $sqlStr = "
                    SELECT 
                        l.codigo_local,
                        l.nome_local,
                        l.codigo_deposito,
                        d.nome_deposito
                    FROM 
                        locais l
                    INNER JOIN 
                        depositos d ON l.codigo_deposito = d.codigo_deposito
                ";
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Executa a consulta
                $sql->execute();
        
                // Retorna os resultados como um array associativo
                return $sql->fetchAll(\PDO::FETCH_ASSOC);
        
            } catch (\PDOException $e) {
                // Registra o erro no log
                error_log("Erro na consulta SQL: " . $e->getMessage());
        
                // Retorna uma mensagem genérica
                return ['erro' => 'Ocorreu um problema ao buscar os locais e depósitos. Tente novamente mais tarde.'];
            }
        }
        
    }
?>