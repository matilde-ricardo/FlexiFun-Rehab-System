using UnityEngine;

public class RehabSessionManager : MonoBehaviour
{
    [Header("Inputs")]
    public GloveSerialReader glove;

    [Header("Target (sem calibração)")]
    [Range(0f, 1f)] public float targetPercent = 0.65f;
    public int fixedMax = 600;      // <<< ajusta depois (ex: 600, 1200, 2000, 4095)
    public int bandHalfWidth = 50;

    [Header("Runtime (read-only)")]
    public int target, minTarget, maxTarget;

    void Start()
    {
        SetupTargets();
    }

    void SetupTargets()
    {
        int max = Mathf.Max(1, fixedMax);
        target = Mathf.RoundToInt(max * targetPercent);
        minTarget = Mathf.Max(0, target - bandHalfWidth);
        maxTarget = target + bandHalfWidth;

        Debug.Log($"[Rehab] Sem calibração. Zona: {minTarget}-{maxTarget} (max fixo={max})");
    }

    public bool IsInTargetBand(int fsrValue)
    {
        return fsrValue >= minTarget && fsrValue <= maxTarget;
    }
}