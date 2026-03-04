using UnityEngine;
using UnityEngine.SceneManagement;

public class LevelComplete : MonoBehaviour
{
    [Header("UI Root")]
    public GameObject canvasRoot;

    [Header("Scenes")]
    public string level1SceneName = "Nivel_1";
    public string nextLevelSceneName = "Nivel_2";
    public string mainMenuSceneName = "Menu_Principal";

    [Header("References")]
    public GameStats gameStats; // arrasta aqui o objeto que tem o GameStats

    private bool isOpen = false;

    private void Awake()
    {
        if (canvasRoot != null)
            canvasRoot.SetActive(false);
    }

    public void Show()
    {
        if (canvasRoot != null)
            canvasRoot.SetActive(true);

        isOpen = true;
        Time.timeScale = 0f;
        Cursor.lockState = CursorLockMode.None;
        Cursor.visible = true;
    }

    public void Hide()
    {
        if (canvasRoot != null)
            canvasRoot.SetActive(false);

        isOpen = false;
        Time.timeScale = 1f;
        Cursor.lockState = CursorLockMode.Locked;
        Cursor.visible = false;
    }

    // "Voltar a jogar" -> recomeça o nível 1 do início + moedas a zero
    public void OnContinue()
    {
        Time.timeScale = 1f;

        if (gameStats != null)
            gameStats.ResetStats();
        else
            Debug.LogWarning("LevelComplete: GameStats não está ligado no Inspector!");

        SceneManager.LoadScene(level1SceneName);
    }

    public void OnNextLevel()
    {
        Time.timeScale = 1f;
        SceneManager.LoadScene(nextLevelSceneName);
    }

    public void OnMainMenu()
    {
        Time.timeScale = 1f;
        SceneManager.LoadScene(mainMenuSceneName);
    }
}