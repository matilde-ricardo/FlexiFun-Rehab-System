using UnityEngine;
using UnityEngine.UI;

public class ForceUI : MonoBehaviour
{
    [Header("UI References")]
    public Image forceFill;        // arrasta o ForceBar_Fill (Image)
    public TMPro.TMP_Text debugText; // opcional: arrasta o texto que mostra o valor

    [Header("Tuning")]
    public float maxForce = 300f;

    public void SetForce(float fsr)
    {
        float t = Mathf.Clamp01(fsr / maxForce);

        if (forceFill != null)
            forceFill.fillAmount = t;

        if (debugText != null)
            debugText.text = $"FSR: {fsr:0}  Fill: {t:0.00}";
    }
}
