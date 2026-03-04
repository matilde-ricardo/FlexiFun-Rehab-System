using UnityEngine;
using StarterAssets;

public class AutoRun : MonoBehaviour
{
    public StarterAssetsInputs inputs;
    public bool enableAutoRun = true;
    public float forward = 1f; // 1 = andar para a frente sempre

    void Reset()
    {
        inputs = GetComponent<StarterAssetsInputs>();
    }

    void Update()
    {
        if (!enableAutoRun || inputs == null) return;

        // move.y = forward/back | move.x = esquerda/direita
        inputs.move = new Vector2(0f, forward);

        // opcional: desativa sprint
        inputs.sprint = false;
    }
}