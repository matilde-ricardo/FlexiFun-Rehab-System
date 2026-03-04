using TMPro;
using UnityEngine;

public class GameStats : MonoBehaviour
{
    [Header("Coins")]
    public int coins = 0;
    public int coinsGoal = 10;          // objetivo (10 moedas)
    public TextMeshProUGUI coinsText;

    [Header("Level Complete UI")]
    public LevelComplete levelComplete; // arrasta aqui o script LevelComplete

    private bool completed = false;

    void Start()
    {
        UpdateCoinsUI();
    }

    public void AddCoin(int amount = 1)
    {
        if (completed) return;

        coins += amount;

        // não passar do objetivo
        if (coins > coinsGoal)
            coins = coinsGoal;

        UpdateCoinsUI();

        // quando chega ao objetivo, mostra o menu
        if (coins >= coinsGoal)
        {
            completed = true;

            if (levelComplete != null)
                levelComplete.Show();
            else
                Debug.LogWarning("GameStats: LevelComplete não está ligado no Inspector!");
        }
    }

    void UpdateCoinsUI()
    {
        if (coinsText != null)
            coinsText.text = $"{coins}/{coinsGoal}";
    }

    public void ResetStats()
{
    completed = false;
    coins = 0;
    UpdateCoinsUI();
}
}


