using System.Collections;
using TMPro;
using UnityEngine;

public class UIMessages : MonoBehaviour
{
    public TextMeshProUGUI hintText;
    public TextMeshProUGUI feedbackText;

    Coroutine feedbackRoutine;

    void Start()
    {
        HideHint();
        ClearFeedback();
    }

    public void ShowHint(string msg)
    {
        if (hintText == null) return;
        hintText.text = msg;
        hintText.gameObject.SetActive(true);
    }

    public void HideHint()
    {
        if (hintText == null) return;
        hintText.text = "";
        hintText.gameObject.SetActive(false);
    }

    public void ShowFeedback(string msg, float seconds = 1.5f)
    {
        if (feedbackText == null) return;

        if (feedbackRoutine != null)
            StopCoroutine(feedbackRoutine);

        feedbackRoutine = StartCoroutine(FeedbackRoutine(msg, seconds));
    }

    IEnumerator FeedbackRoutine(string msg, float seconds)
    {
        feedbackText.text = msg;
        feedbackText.gameObject.SetActive(true);

        yield return new WaitForSecondsRealtime(seconds);

        ClearFeedback();
    }

    void ClearFeedback()
    {
        if (feedbackText == null) return;
        feedbackText.text = "";
        feedbackText.gameObject.SetActive(false);
    }
}