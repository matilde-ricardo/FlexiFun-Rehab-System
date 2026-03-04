using UnityEngine;

public class CoinPickupSimple : MonoBehaviour
{
    public float spinSpeed = 180f;
    public AudioClip pickupSound;
    public float volume = 1f;

    public int forceThreshold = 300;

    private GloveSerialReader glove;
    private UIMessages ui;

    private bool collected = false;

    void Start()
    {
        glove = FindFirstObjectByType<GloveSerialReader>();
        if (glove == null)
            Debug.LogError("[Coin] GloveSerialReader não encontrado na cena!");

        ui = FindFirstObjectByType<UIMessages>(); // pode ser null, tudo bem
    }

    void Update()
    {
        transform.Rotate(0f, spinSpeed * Time.deltaTime, 0f, Space.World);
    }

    private void OnTriggerStay(Collider other)
    {
        if (collected) return;
        if (!other.CompareTag("Player")) return;
        if (glove == null) return;

        if (glove.fsrRaw > forceThreshold)
        {
            collected = true;

            // Evita trigger repetido antes de destruir
            var col = GetComponent<Collider>();
            if (col != null) col.enabled = false;

            if (pickupSound != null)
                AudioSource.PlayClipAtPoint(pickupSound, transform.position, volume);

            // contador
            var stats = FindFirstObjectByType<GameStats>();
            if (stats != null) stats.AddCoin(1);

            // feedback UI
            ui?.ShowFeedback("Moeda coletada com sucesso!", 1.5f);

            Destroy(gameObject);
        }
    }
}