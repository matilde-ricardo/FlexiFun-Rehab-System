using UnityEngine;
using UnityEngine.SceneManagement;

public class LevelCompleteMenu : MonoBehaviour
{
    [Header("References")]
    public GameStats stats;          // arrasta o GameStats aqui
    public GameObject panel;         // arrasta o LevelCompletePanel aqui

    [Header("Scenes")]
    public int nextLevelBuildIndex = 1;     // build index do próximo nível
    public int mainMenuBuildIndex = 0;      // build index do menu principal

    bool shown = false;

    void Start()
    {
        if (panel != null)
            panel.SetActive(false);

        if (stats == null)
            stats = FindFirstObjectByType<GameStats>();
    }

    void Update()
    {
        if (shown || stats == null) return;

        // Mostra quando atingir o objetivo (10/10 por defeito)
        if (stats.coins >= stats.coinsGoal)
        {
            Show();
        }
    }

    void Show()
    {
        shown = true;

        if (panel != null)
            panel.SetActive(true);

        // Pausa o jogo
        Time.timeScale = 0f;

        // Cursor visível (útil se tens FPS controller)
        Cursor.lockState = CursorLockMode.None;
        Cursor.visible = true;
    }

    // ----------------- BOTÕES -----------------

    // "Voltar a jogar" (recomeçar o nível atual)
    public void ReplayLevel()
    {
        Time.timeScale = 1f;
        SceneManager.LoadScene(SceneManager.GetActiveScene().buildIndex);
    }

    // "Avançar próximo nível"
    public void NextLevel()
    {
        Time.timeScale = 1f;
        SceneManager.LoadScene(nextLevelBuildIndex);
    }

    // "Sair para o menu principal"
    public void BackToMainMenu()
    {
        Time.timeScale = 1f;
        SceneManager.LoadScene(mainMenuBuildIndex);
    }
}