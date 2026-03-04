using UnityEngine;
using UnityEngine.SceneManagement; // Necessário para trocar de cena

public class MenuCreditos : MonoBehaviour
{
    // Substitui pelo nome exato da tua cena de menu inicial
    [SerializeField] private string nomeDoMenuInicial = "Menu_Principal";

    public void VoltarAoInicio()
    {
        // Se o jogo estava pausado ou com timeScale alterado, resetamos aqui por segurança
        Time.timeScale = 1f;
        
        // Carrega a cena do menu
        SceneManager.LoadScene(nomeDoMenuInicial);
    }
}