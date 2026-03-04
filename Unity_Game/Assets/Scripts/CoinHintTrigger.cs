using UnityEngine;

public class CoinHintTrigger : MonoBehaviour
{
    [Header("References")]
    public UIMessages ui;                 // arrasta aqui o teu objeto UIMessages (ou deixa vazio e ele procura)

    [Header("Trigger settings")]
    public string playerTag = "Player";

    [Header("Message")]
    public string hintMessage = "Apertar mão para coletar moeda";

    private int insideCount = 0;

    private void Awake()
    {
        // Se não arrastares no Inspector, tenta encontrar automaticamente
        if (ui == null) ui = FindFirstObjectByType<UIMessages>();
    }

    private void OnTriggerEnter(Collider other)
    {
        if (!other.CompareTag(playerTag)) return;

        insideCount++;
        if (insideCount == 1)
            ui?.ShowHint(hintMessage);
    }

    private void OnTriggerExit(Collider other)
    {
        if (!other.CompareTag(playerTag)) return;

        insideCount--;
        if (insideCount <= 0)
        {
            insideCount = 0;
            ui?.HideHint();
        }
    }

    private void OnDisable()
    {
        // Se a moeda desaparecer/desativar ao ser coletada, garante que a hint some
        ui?.HideHint();
    }

    private void OnDestroy()
    {
        ui?.HideHint();
    }
}
