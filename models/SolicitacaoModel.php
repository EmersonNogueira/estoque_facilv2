<?php
	namespace models;

	class SolicitacaoModel extends Model
	{
        public function listarSolicitacoes() {
            try {
                // Consulta para retornar apenas as solicitações com status 'Pendente'
                $sql = "SELECT 
                            s.codigo_solicitacao,
                            u.nome AS usuario_criador,
                            s.solicitante,
                            s.setor,
                            s.subsetor,
                            DATE_FORMAT(s.data, '%d/%m/%Y') AS data,  -- Formata a data
                            s.status,
                            -- Calcula o total de produtos e a quantidade total solicitada
                            (SELECT COUNT(si.codigo_item) FROM solicitacao_item si WHERE si.codigo_solicitacao = s.codigo_solicitacao) AS total_itens,
                            (SELECT SUM(si.quantidade) FROM solicitacao_item si WHERE si.codigo_solicitacao = s.codigo_solicitacao) AS quantidade_total
                        FROM 
                            solicitacoes s
                        JOIN 
                            usuario u ON s.usuario_criador = u.id
                        WHERE 
                            s.status = 'Pendente'  -- Filtra as solicitações com status 'Pendente'
                        ORDER BY 
                            s.data ASC";  // Ordena por data

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                
                // Retorna as solicitações como um array associativo
                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
                
            } catch (\PDOException $e) {
                error_log("Erro ao listar solicitações: " . $e->getMessage());
                die("Erro ao listar solicitações: " . $e->getMessage());
            }
        }


        public function produtosSol2($id) {
            try {
                // Consulta para buscar os produtos associados à solicitação e os nomes dos produtos
                $sql = "SELECT 
                            sp.codigo_item, 
                            p.descricao AS nome_produto, 
                            sp.quantidade, 
                            COALESCE(SUM(e.saldo), 0) AS saldo,  
                            p.custo_unitario AS custo_unitario
                        FROM solicitacao_item sp
                        INNER JOIN itens p ON sp.codigo_item = p.codigo_item
                        LEFT JOIN estoques e ON sp.codigo_item = e.codigo_item
                        WHERE sp.codigo_solicitacao = :codigo_solicitacao
                        GROUP BY 
                            sp.codigo_item, 
                            p.descricao, 
                            sp.quantidade, 
                            p.custo_unitario";

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindValue(':codigo_solicitacao', $id, \PDO::PARAM_INT);
                $stmt->execute();

                // Retorna os produtos como um array associativo, incluindo nome do produto e saldo calculado
                $produtos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                return $produtos;

            } catch (\PDOException $e) {
                error_log("Erro ao buscar produtos: " . $e->getMessage());
                echo json_encode(['erro' => "Erro ao buscar produtos: " . $e->getMessage()]);
            }
        }

public function produtosSol($codigo_solicitacao) {
    try {
        // 1. Buscar os produtos solicitados
        $sql = "
            SELECT 
                si.codigo_item,
                i.descricao AS nome_produto,
                si.quantidade AS quantidade_solicitada,
                i.custo_unitario
            FROM solicitacao_item si
            INNER JOIN itens i ON si.codigo_item = i.codigo_item
            WHERE si.codigo_solicitacao = :codigo_solicitacao
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':codigo_solicitacao', $codigo_solicitacao, \PDO::PARAM_INT);
        $stmt->execute();
        $produtos = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Agora, para cada produto, buscamos o saldo total disponível no estoque
        foreach ($produtos as &$produto) {
            $sqlSaldo = "
                SELECT COALESCE(SUM(saldo), 0) as saldo_total
                FROM estoques
                WHERE codigo_item = :codigo_item
            ";
            $stmtSaldo = $this->pdo->prepare($sqlSaldo);
            $stmtSaldo->bindValue(':codigo_item', $produto['codigo_item'], \PDO::PARAM_INT);
            $stmtSaldo->execute();
            $resultadoSaldo = $stmtSaldo->fetch(\PDO::FETCH_ASSOC);

            $produto['saldo'] = $resultadoSaldo['saldo_total'];
        }




        // 2. Para cada produto, buscar os estoques com validade e local
        foreach ($produtos as &$produto) {
            $sqlEstoque = "
                SELECT
                    e.id, 
                    e.validade,
                    e.saldo,
                    d.nome_deposito,
                    l.nome_local
                FROM estoques e
                INNER JOIN locais l ON e.codigo_local = l.codigo_local
                INNER JOIN depositos d ON l.codigo_deposito = d.codigo_deposito
                WHERE e.codigo_item = :codigo_item AND e.saldo > 0
                ORDER BY e.validade IS NULL ASC, e.validade ASC
            ";

            $stmtEstoque = $this->pdo->prepare($sqlEstoque);
            $stmtEstoque->bindValue(':codigo_item', $produto['codigo_item'], \PDO::PARAM_INT);
            $stmtEstoque->execute();
            $locais = $stmtEstoque->fetchAll(\PDO::FETCH_ASSOC);

            $quantidade_necessaria = $produto['quantidade_solicitada'];
            $quantidade_acumulada = 0;
            $produto['estoque'] = [];

            foreach ($locais as $local) {
                if ($quantidade_acumulada >= $quantidade_necessaria) break;

                $quantidade_disponivel = $local['saldo'];
                $quantidade_para_retirar = min($quantidade_disponivel, $quantidade_necessaria - $quantidade_acumulada);

                $produto['estoque'][] = [
                    'validade' => $local['validade'],
                    'saldo' => $quantidade_para_retirar,
                    'nome_deposito' => $local['nome_deposito'],
                    'nome_local' => $local['nome_local'],
                    'id_estoque' => $local['id']
                ];

                $quantidade_acumulada += $quantidade_para_retirar;
            }

            $produto['saldo_total_localizado'] = $quantidade_acumulada;
            $produto['atende_total'] = ($quantidade_acumulada >= $quantidade_necessaria);
        }

        return $produtos;

    } catch (\PDOException $e) {
        error_log("Erro ao buscar produtos: " . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['erro' => "Erro ao buscar produtos: " . $e->getMessage()]);
        die();
    }
}


		public function alterarQuantidadeProduto($idSolicitacao, $idProduto, $novaQuantidade) {
			try {
				// Consulta para atualizar a quantidade de um produto na tabela solicitacao_produto
				$sql = "UPDATE solicitacao_item 
						SET quantidade = :novaQuantidade 
						WHERE codigo_solicitacao = :idSolicitacao AND codigo_item = :idProduto";
				
				// Preparação da consulta
				$stmt = $this->pdo->prepare($sql);
				
				// Bind dos parâmetros
				$stmt->bindValue(':novaQuantidade', $novaQuantidade, \PDO::PARAM_INT);
				$stmt->bindValue(':idSolicitacao', $idSolicitacao, \PDO::PARAM_INT);
				$stmt->bindValue(':idProduto', $idProduto, \PDO::PARAM_INT);
				
				// Execução da consulta
				$stmt->execute();
				
				// Verifica se a atualização foi bem-sucedida
				if ($stmt->rowCount() > 0) {
					return ['sucesso' => 'Quantidade atualizada com sucesso.'];
				} else {
					return ['erro' => 'Nenhuma alteração foi realizada.'];
				}
				
			} catch (\PDOException $e) {
				error_log("Erro ao atualizar quantidade do produto: " . $e->getMessage());
				return ['erro' => 'Erro ao atualizar quantidade: ' . $e->getMessage()];
			}
		}
        
    	public function listarSolicitacoesfinal($offset = 0, $limit = 10) {
			try {
				// Consulta para retornar apenas as solicitações com status 'finalizado'
				$sql = "SELECT 
							s.codigo_solicitacao,
							u.nome AS usuario_criador,
							s.solicitante,
							s.setor,
							s.subsetor,
							DATE_FORMAT(s.data, '%d/%m/%Y') AS data,
							s.status,
							s.receptor,
							DATE_FORMAT((
								SELECT MIN(r.data_registro)
								FROM registros r
								WHERE r.codigo_solicitacao = s.codigo_solicitacao
								AND r.tipo = 'solicitacao'
							), '%d/%m/%Y') AS data_finalizacao
						FROM 
							solicitacoes s
						JOIN 
							usuario u ON s.usuario_criador = u.id
						WHERE 
							s.status = 'Concluída'
						ORDER BY (
							SELECT MIN(r.data_registro)
							FROM registros r
							WHERE r.codigo_solicitacao = s.codigo_solicitacao
							AND r.tipo = 'solicitacao'
						) DESC
						LIMIT :offset, :limit";  // Limita os resultados
		
				$stmt = $this->pdo->prepare($sql);
				$stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
				$stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
				$stmt->execute();
		
				// Retorna as solicitações como um array associativo
				return $stmt->fetchAll(\PDO::FETCH_ASSOC);
			} catch (\PDOException $e) {
				error_log("Erro ao listar solicitações: " . $e->getMessage());
				die("Erro ao listar solicitações: " . $e->getMessage());
			}
		}

		public function contarSolicitacoesfinal() {
			try {
				// Consulta para contar o total de solicitações 'finalizado'
				$sql = "SELECT COUNT(*) AS total FROM solicitacoes WHERE status = 'Concluída'";
				$stmt = $this->pdo->prepare($sql);
				$stmt->execute();
				$result = $stmt->fetch(\PDO::FETCH_ASSOC);
				return $result['total'];
			} catch (\PDOException $e) {
				error_log("Erro ao contar solicitações: " . $e->getMessage());
				die("Erro ao contar solicitações: " . $e->getMessage());
			}
		}
        public function listarSolicitacoesPorSetor($offset = 0, $limit = 10) {
            try {
                $setor = $_SESSION['setor'];

                $sql = "SELECT 
                            s.codigo_solicitacao,
                            u.nome AS usuario_criador,
                            s.solicitante,
                            s.setor,
                            s.subsetor,
                            DATE_FORMAT(s.data, '%d/%m/%Y') AS data,
                            s.status,
                            s.receptor,
                            DATE_FORMAT((
                                SELECT MIN(r.data_registro)
                                FROM registros r
                                WHERE r.codigo_solicitacao = s.codigo_solicitacao
                                AND r.tipo = 'solicitacao'
                            ), '%d/%m/%Y') AS data_finalizacao
                        FROM 
                            solicitacoes s
                        JOIN 
                            usuario u ON s.usuario_criador = u.id
                        WHERE 
                            s.setor = :setor
                        ORDER BY 
                            CASE WHEN s.status = 'Pendente' THEN 0 ELSE 1 END,
                            s.data DESC
                        LIMIT :offset, :limit";

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':setor', $setor, \PDO::PARAM_STR);
                $stmt->bindParam(':offset', $offset, \PDO::PARAM_INT);
                $stmt->bindParam(':limit', $limit, \PDO::PARAM_INT);
                $stmt->execute();

                return $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\PDOException $e) {
                error_log("Erro ao listar solicitações do setor: " . $e->getMessage());
                die("Erro ao listar solicitações do setor: " . $e->getMessage());
            }
        }

        public function contarSolicitacoesPorSetor() {
            try {
                $setor = $_SESSION['setor'];

                $sql = "SELECT COUNT(*) AS total 
                        FROM solicitacoes 
                        WHERE setor = :setor";

                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':setor', $setor, \PDO::PARAM_STR);
                $stmt->execute();
                $result = $stmt->fetch(\PDO::FETCH_ASSOC);
                return $result['total'];
            } catch (\PDOException $e) {
                error_log("Erro ao contar solicitações do setor: " . $e->getMessage());
                die("Erro ao contar solicitações do setor: " . $e->getMessage());
            }
        }



        
        public function reader() {
            try {
                $search = isset($_GET['search']) ? $_GET['search'] : '';

                // Base da SQL
                $sqlStr = "SELECT i.codigo_item, i.descricao, SUM(e.saldo) AS saldo, i.custo_unitario, i.situacao
                        FROM itens i
                        LEFT JOIN estoques e ON i.codigo_item = e.codigo_item";

                // Filtro de visibilidade e busca
                $where = [];

                if (!isset($_SESSION['tipo']) || ($_SESSION['tipo'] !== 'admin' && $_SESSION['tipo'] !== 'infra')) {
                    $where[] = "i.visivel = 1";
                }

                if (!empty($search)) {
                    $where[] = "i.descricao LIKE :search";
                }

                // Aplica WHERE se necessário
                if (!empty($where)) {
                    $sqlStr .= " WHERE " . implode(' AND ', $where);
                }

                // Agrupamento correto
                $sqlStr .= " GROUP BY i.codigo_item, i.descricao, i.custo_unitario";

                // Filtro por saldo > 0 após o GROUP BY
                $sqlStr .= " HAVING SUM(e.saldo) > 0";

                // Prepara e executa
                $sql = $this->pdo->prepare($sqlStr);

                if (!empty($search)) {
                    $sql->bindValue(':search', '%' . $search . '%');
                }

                $sql->execute();
                return $sql->fetchAll(\PDO::FETCH_ASSOC);

            } catch (\PDOException $e) {
                error_log("Erro na consulta: " . $e->getMessage());
                die("Erro na consulta: " . $e->getMessage());
            }
        }

		// Supondo que você já tenha uma conexão com o banco de dados ($this->pdo)
		public function inserirSolicitacao($dados) {
			try {
				$this->pdo->beginTransaction();
		
				// Inserir na tabela `solicitacoes`
				$sqlSolicitacao = "INSERT INTO solicitacoes (usuario_criador, solicitante, setor, subsetor, data, status) 
								VALUES (:usuario_criador, :solicitante, :setor, :subsetor, NOW(), 'Pendente')";
				$stmtSolicitacao = $this->pdo->prepare($sqlSolicitacao);
				$stmtSolicitacao->bindValue(':usuario_criador', $dados['usuario_id']);
				$stmtSolicitacao->bindValue(':solicitante', $dados['solicitante']);
				$stmtSolicitacao->bindValue(':setor', $dados['setor']);
				$stmtSolicitacao->bindValue(':subsetor', $dados['subsetor']);
				$stmtSolicitacao->execute();
		
				$idSolicitacao = $this->pdo->lastInsertId();
		
				// Inserir na tabela `produtos_solicitacao`
				$sqlProdutoSolicitacao = "INSERT INTO solicitacao_item (codigo_solicitacao, codigo_item, quantidade) 
										VALUES (:id_solicitacao, :id_produto, :quantidade)";
				$stmtProdutoSolicitacao = $this->pdo->prepare($sqlProdutoSolicitacao);
		
				foreach ($dados['produtos'] as $produto) {
					$stmtProdutoSolicitacao->bindValue(':id_solicitacao', $idSolicitacao);
					$stmtProdutoSolicitacao->bindValue(':id_produto', $produto['id_produto']);
					$stmtProdutoSolicitacao->bindValue(':quantidade', $produto['quantidade']);
					$stmtProdutoSolicitacao->execute();
				}
		
				$this->pdo->commit();
		
				// Define a mensagem de confirmação após sucesso total
				$this->setMensagemConfirmacao("Solicitação criada com sucesso, procure o setor de infraestrutura para pegar seus produtos.");
		
			} catch (\PDOException $e) {
				$this->pdo->rollBack();
				error_log("Erro ao inserir solicitação: " . $e->getMessage());
				die("Erro ao inserir solicitação: " . $e->getMessage());
			}
		}

        public function getSaldoProduto($codigoItem) {
            if (!$this->pdo) {
                throw new \Exception("\$this->pdo não está definido ou não é um PDO válido.");
            }

            try {
                $sql = "SELECT COALESCE(SUM(saldo), 0) AS total_saldo 
                        FROM estoques 
                        WHERE codigo_item = :codigo_item";
                $stmt = $this->pdo->prepare($sql);
                $stmt->bindParam(':codigo_item', $codigoItem, \PDO::PARAM_STR);
                $stmt->execute();
                $saldoRow = $stmt->fetch(\PDO::FETCH_ASSOC);

                return $saldoRow && isset($saldoRow['total_saldo']) ? (int)$saldoRow['total_saldo'] : 0;

            } catch (\PDOException $e) {
                error_log("Erro no getSaldoProduto: " . $e->getMessage());
                return 0; // Evita erro 500 no adicionarCarrinho
            }
        }

		
		// Função helper para definir a mensagem de confirmação
		private function setMensagemConfirmacao($mensagem) {
			$_SESSION['mensagem_confirmacao'] = $mensagem;
		}        

		



                
    }
?>

