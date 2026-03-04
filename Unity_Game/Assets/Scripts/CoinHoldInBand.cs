using UnityEngine;

public class CoinHoldInBand : MonoBehaviour
{
    [Header("Visual")]
    public float spinSpeed = 180f;

    [Header("Collect (sem Rehab)")]
    public int minForceToCount = 200;   // ajusta ao teu FSR
    public float holdTime = 2f;

    [Header("Audio")]
    public AudioClip pickupSound;
    public float volume = 1f;

    [Header("UI Messages")]
    public string hintMessage = "Apertar mão para coletar moeda";
    public string successMessage = "Moeda coletada com sucesso!";
    public float successDuration = 1.5f;

    GloveSerialReader glove;
    GameStats stats;
    UIMessages ui;

    float timer = 0f;
    float logEvery = 0f;

    void Awake()
    {
        // GARANTIR que triggers funcionam com CharacterController
        var col = GetComponent<Collider>();
        if (col == null)
        {
            Debug.LogError("[Coin] ERRO: a moeda não tem Collider!");
        }
        else
        {
            col.isTrigger = true; // força trigger ON
        }

        var rb = GetComponent<Rigidbody>();
        if (rb == null)
        {
            rb = gameObject.AddComponent<Rigidbody>();
            Debug.Log("[Coin] Adicionei Rigidbody kinematic à moeda (para triggers).");
        }
        rb.isKinematic = true;
        rb.useGravity = false;
    }

    void Start()
    {
        glove = FindFirstObjectByType<GloveSerialReader>();
        stats = FindFirstObjectByType<GameStats>();
        ui = FindFirstObjectByType<UIMessages>();

        Debug.Log("[Coin] Start() glove=" + (glove != null) + " stats=" + (stats != null) + " ui=" + (ui != null));
    }

    void Update()
    {
        transform.Rotate(0f, spinSpeed * Time.deltaTime, 0f, Space.World);
    }

    void OnTriggerEnter(Collider other)
    {
        Debug.Log("[Coin] ENTER " + other.name + " tag=" + other.tag);

        if (!other.CompareTag("Player")) return;

        timer = 0f;
        if (ui == null) ui = FindFirstObjectByType<UIMessages>();
        ui?.ShowHint(hintMessage);
    }

    void OnTriggerExit(Collider other)
    {
        Debug.Log("[Coin] EXIT " + other.name + " tag=" + other.tag);

        if (!other.CompareTag("Player")) return;

        timer = 0f;
        if (ui == null) ui = FindFirstObjectByType<UIMessages>();
        ui?.HideHint();
    }

    void OnTriggerStay(Collider other)
    {
        if (!other.CompareTag("Player")) return;

        if (glove == null) glove = FindFirstObjectByType<GloveSerialReader>();
        if (glove == null) return;

        // Debug a cada 0.5s
        logEvery += Time.deltaTime;
        if (logEvery >= 0.5f)
        {
            logEvery = 0f;
            Debug.Log($"[Coin] Stay fsrRaw={glove.fsrRaw} timer={timer:0.00} minForce={minForceToCount}");
        }

        if (glove.fsrRaw >= minForceToCount)
        {
            timer += Time.deltaTime;
            if (timer >= holdTime)
                Collect();
        }
        else
        {
            timer = 0f;
        }
    }

    void Collect()
    {
        Debug.Log("[Coin] COLETADA!");

        if (pickupSound != null)
            AudioSource.PlayClipAtPoint(pickupSound, transform.position, volume);

        if (stats == null) stats = FindFirstObjectByType<GameStats>();
        stats?.AddCoin(1);

        if (ui == null) ui = FindFirstObjectByType<UIMessages>();
        if (ui != null)
        {
            ui.HideHint();
            ui.ShowFeedback(successMessage, successDuration);
        }

        Destroy(gameObject);
    }
}