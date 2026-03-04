using UnityEngine;

public class IMUTurnPlayer : MonoBehaviour
{
    public GloveSerialReader glove;
    public Transform player;                 // PlayerArmature (root)
    public float gain = 1.0f;                // se estiver ao contrário mete -1
    public float deadzoneDeg = 2.0f;         // evita tremores
    public float maxTurnSpeed = 180f;        // deg/seg (suavidade)

    private float yawZero;
    private Quaternion baseRot;
    private bool calibrated;

    void Start()
    {
        Calibrate();
    }

    public void Calibrate()
    {
        if (glove == null || player == null) return;   //segurar a moeda e não largar durante 2s

        yawZero = glove.yawDeg;      // “zero” da mão
        baseRot = player.rotation;   // rotação atual do player
        calibrated = true;

        Debug.Log("[IMU] Calibrado");
    }

    void Update()
    {
        if (!calibrated || glove == null || player == null) return;

        float delta = (glove.yawDeg - yawZero) * gain;

        // deadzone
        if (Mathf.Abs(delta) < deadzoneDeg) delta = 0f;

        Quaternion target = baseRot * Quaternion.Euler(0f, delta, 0f);

        player.rotation = Quaternion.RotateTowards(
            player.rotation, target, maxTurnSpeed * Time.deltaTime
        );

        // tecla para recalibrar (opcional)
        if (Input.GetKeyDown(KeyCode.R))
            Calibrate();
    }
}