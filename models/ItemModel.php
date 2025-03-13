<?php
    namespace models;

    class ItemModel extends Model
    {
        public function itens_locais() {
            try {
                // Captura o parâmetro de busca, se presente
                $search = isset($_GET['search']) ? $_GET['search'] : '';
                
                // Consulta SQL com JOIN entre itens e estoque
                $sqlStr = "
                    SELECT 
                        i.codigo_item, 
                        i.descricao, 
                        i.situacao, 
                        i.visivel, 
                        i.custo_unitario, 
                        i.categoria, 
                        e.codigo_local, 
                        e.saldo, 
                        e.validade
                    FROM 
                        itens i
                    LEFT JOIN 
                        estoque e ON i.codigo_item = e.codigo_item
                ";
        
                // Adiciona o filtro de busca, se houver
                if (!empty($search)) {
                    $sqlStr .= " WHERE i.descricao LIKE :search OR i.categoria LIKE :search";
                }
        
                // Adiciona a ordenação
                $sqlStr .= " ORDER BY i.categoria ASC, i.descricao ASC";
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Se houver busca, vincula o parâmetro
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%');
                }
        
                // Executa a consulta
                $sql->execute();
        
                // Retorna os resultados como um array associativo
                return $sql->fetchAll(\PDO::FETCH_ASSOC);
        
            } catch (\PDOException $e) {
                // Registra o erro no log
                error_log("Erro na consulta SQL: " . $e->getMessage());
        
                // Retorna uma mensagem genérica
                return ['erro' => 'Ocorreu um problema ao buscar os itens. Tente novamente mais tarde.'];
            }
        }

        public function itens_saldo() {
            try {
                // Captura o parâmetro de busca, se presente
                $search = isset($_GET['search']) ? $_GET['search'] : '';
        
                // Consulta SQL agrupando por código do item e somando o saldo
                $sqlStr = "
                    SELECT 
                        i.codigo_item, 
                        i.descricao, 
                        i.situacao, 
                        i.visivel, 
                        i.custo_unitario, 
                        i.categoria, 
                        SUM(e.saldo) AS saldo_total
                    FROM 
                        itens i
                    LEFT JOIN 
                        estoque e ON i.codigo_item = e.codigo_item
                    GROUP BY 
                        i.codigo_item, i.descricao, i.situacao, i.visivel, i.custo_unitario, i.categoria
                    ORDER BY 
                        i.categoria ASC, i.descricao ASC;
                ";
        
                // Adiciona o filtro de busca, se houver
                if (!empty($search)) {
                    $sqlStr .= " WHERE i.descricao LIKE :search OR i.categoria LIKE :search";
                }
        
                // Adiciona o agrupamento e a ordenação
                $sqlStr .= " GROUP BY i.codigo_item, i.descricao, i.situacao, i.visivel, i.custo_unitario, i.categoria 
                             ORDER BY i.categoria ASC, i.descricao ASC";
        
                // Prepara a consulta SQL
                $sql = $this->pdo->prepare($sqlStr);
        
                // Se houver busca, vincula o parâmetro
                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%');
                }
        
                // Executa a consulta
                $sql->execute();
        
                // Retorna os resultados como um array associativo
                return $sql->fetchAll(\PDO::FETCH_ASSOC);
        
            } catch (\PDOException $e) {
                // Registra o erro no log
                error_log("Erro na consulta SQL: " . $e->getMessage());
        
                // Retorna uma mensagem genérica
                return ['erro' => 'Ocorreu um problema ao buscar os itens. Tente novamente mais tarde.'];
            }
        }
        
        
        

    }
?>